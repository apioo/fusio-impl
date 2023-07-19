<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Service;

use Fusio\Engine\User\ProviderInterface;
use Fusio\Engine\User\UserDetails;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Identity\CreatedEvent;
use Fusio\Impl\Event\Identity\DeletedEvent;
use Fusio\Impl\Event\Identity\UpdatedEvent;
use Fusio\Impl\Provider\UserProvider;
use Fusio\Impl\Service;
use Fusio\Model;
use Fusio\Model\Backend\UserRemote;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\Tests\LocalDateTest;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;
use PSX\Sql\Condition;
use PSX\Uri\Uri;
use PSX\Uri\Url;

/**
 * Identity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Identity
{
    private Table\Identity $identityTable;
    private Table\IdentityRequest $identityRequestTable;
    private Service\Category\Validator $validator;
    private UserProvider $userProvider;
    private Service\User $userService;
    private Service\App\Token $appTokenService;
    private ConfigInterface $config;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(/*Table\Identity $identityTable, Table\IdentityRequest $identityRequestTable, */Identity\Validator $validator, UserProvider $userProvider, Service\User $userService, Service\App\Token $appTokenService, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->identityTable = $identityTable;
        $this->identityRequestTable = $identityRequestTable;
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->userService = $userService;
        $this->appTokenService = $appTokenService;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(IdentityCreate $identity, UserContext $context): int
    {
        $this->validator->assert($identity);

        try {
            $this->identityTable->beginTransaction();

            // create category
            $row = new Table\Generated\IdentityRow();
            $row->setStatus(Table\Identity::STATUS_ACTIVE);
            $row->setName($identity->getName());
            $this->identityTable->create($row);

            $identityId = $this->identityTable->getLastInsertId();
            $identity->setId($identityId);

            $this->identityTable->commit();
        } catch (\Throwable $e) {
            $this->identityTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($identity, $context));

        return $identityId;
    }

    public function update(string $identityId, IdentityUpdate $identity, UserContext $context): int
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $this->validator->assert($identity, $existing);

        try {
            $this->identityTable->beginTransaction();

            // update category
            $existing->setName($identity->getName() ?? $existing->getName());
            $this->identityTable->update($existing);

            $this->identityTable->commit();
        } catch (\Throwable $e) {
            $this->identityTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($identity, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $identityId, UserContext $context): int
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $existing->setStatus(Table\Identity::STATUS_DELETED);
        $this->identityTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function redirect(string $identityId): Uri
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $state = TokenGenerator::generateState();

        $row = new IdentityRequestRow();
        $row->setIdentity($existing->getId());
        $row->setState($state);
        $row->setInsertDate(LocalDateTime::now());
        $this->identityRequestTable->insert($row);

        $authorizationUri = Url::parse($existing->getAuthorizationUri());
        $authorizationUri = $authorizationUri->withParameters([
            'response_type' => 'code',
            'client_id' => $existing->getClientId(),
            'state' => $state,
            'redirect_uri' => $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch') . 'consumer/provider/' . $existing->getId(),
        ]);

        return $this->getProvider($existing)->redirect($authorizationUri);
    }

    public function exchange(string $identityId, string $code, string $clientId, string $redirectUri, string $state): AccessToken
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $condition = Condition::withAnd();
        $condition->equals('id', $existing->getId());
        $condition->equals('state', $state);
        $identityRequest = $this->identityRequestTable->findOneBy($condition);

        if (!$identityRequest instanceof IdentityRequestRow) {
            throw new StatusCode\BadRequestException('Provided identity state was not requested');
        }

        $configuration = new Configuration();
        $user = $this->getProvider($existing)->requestUser($configuration, $code, $clientId, $redirectUri);
        if (!$user instanceof UserDetails) {
            throw new StatusCode\BadRequestException('Could not request user information');
        }

        $remote = new UserRemote();
        $remote->setProvider($existing->getId());
        $remote->setRemoteId($user->getId());
        $remote->setName($user->getUserName());
        $remote->setEmail($user->getEmail());

        $userId = $this->userService->createRemote($remote, UserContext::newAnonymousContext());

        // get scopes for user
        $scopes = $this->userService->getAvailableScopes($userId);

        $appId = $existing->getAppId();
        if (empty($appId)) {
            // if the identity is not assigned to a specific app we use by default the consumer app
            $appId = 2;
        }

        return $this->appTokenService->generateAccessToken(
            $appId,
            $userId,
            $scopes,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->config->get('fusio_expire_token'))
        );
    }

    private function getProvider(IdentityRow $existing): ProviderInterface
    {
        return $this->userProvider->getInstance($existing->getProvider());
    }
}
