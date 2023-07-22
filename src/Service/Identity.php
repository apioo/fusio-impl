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

use Fusio\Engine\User\Configuration;
use Fusio\Engine\User\ProviderInterface;
use Fusio\Engine\User\UserInfo;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Identity\CreatedEvent;
use Fusio\Impl\Event\Identity\DeletedEvent;
use Fusio\Impl\Event\Identity\UpdatedEvent;
use Fusio\Impl\Provider\UserProvider;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
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
    private Table\Generated\IdentityRequestTable $identityRequestTable;
    private Identity\Validator $validator;
    private UserProvider $userProvider;
    private Service\User $userService;
    private Service\App\Token $appTokenService;
    private ConfigInterface $config;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Identity $identityTable, Table\Generated\IdentityRequestTable $identityRequestTable, Identity\Validator $validator, UserProvider $userProvider, Service\User $userService, Service\App\Token $appTokenService, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
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

    public function create(Model\Backend\IdentityCreate $identity, UserContext $context): int
    {
        $this->validator->assert($identity);

        $provider = $this->userProvider->getInstance($identity->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        try {
            $this->identityTable->beginTransaction();

            // create category
            $row = new Table\Generated\IdentityRow();
            $row->setStatus(Table\Identity::STATUS_ACTIVE);
            $row->setAppId($identity->getAppId());
            $row->setRoleId($identity->getRoleId());
            $row->setName($identity->getName());
            $row->setIcon($identity->getIcon());
            $row->setClass($identity->getClass());
            $row->setClientId($identity->getClientId());
            $row->setClientSecret($identity->getClientSecret());
            $row->setAuthorizationUri($identity->getAuthorizationUri() ?? $provider->getAuthorizationUri());
            $row->setTokenUri($identity->getTokenUri() ?? $provider->getTokenUri());
            $row->setUserInfoUri($identity->getUserInfoUri() ?? $provider->getUserInfoUri());
            $row->setIdProperty($identity->getIdProperty() ?? $provider->getIdProperty());
            $row->setNameProperty($identity->getNameProperty() ?? $provider->getNameProperty());
            $row->setEmailProperty($identity->getEmailProperty() ?? $provider->getEmailProperty());
            $row->setAllowCreate($identity->getAllowCreate() ?? true);
            $row->setInsertDate(LocalDateTime::now());
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

    public function update(string $identityId, Model\Backend\IdentityUpdate $identity, UserContext $context): int
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $this->validator->assert($identity, $existing);

        $provider = $this->userProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        try {
            $this->identityTable->beginTransaction();

            // update category
            $existing->setAppId($identity->getAppId() ?? $existing->getAppId());
            $existing->setRoleId($identity->getRoleId() ?? $existing->getRoleId());
            $existing->setName($identity->getName() ?? $existing->getName());
            $existing->setIcon($identity->getIcon() ?? $existing->getIcon());
            $existing->setClientId($identity->getClientId() ?? $existing->getClientId());
            $existing->setClientSecret($identity->getClientSecret() ?? $existing->getClientSecret());
            $existing->setAuthorizationUri($identity->getAuthorizationUri() ?? $existing->getAuthorizationUri());
            $existing->setTokenUri($identity->getTokenUri() ?? $existing->getTokenUri());
            $existing->setUserInfoUri($identity->getUserInfoUri() ?? $existing->getUserInfoUri());
            $existing->setIdProperty($identity->getIdProperty() ?? $existing->getIdProperty());
            $existing->setNameProperty($identity->getNameProperty() ?? $existing->getNameProperty());
            $existing->setEmailProperty($identity->getEmailProperty() ?? $existing->getEmailProperty());
            $existing->setAllowCreate($identity->getAllowCreate() ?? $existing->getAllowCreate());
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

        $provider = $this->userProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        $state = TokenGenerator::generateState();

        $row = new Table\Generated\IdentityRequestRow();
        $row->setIdentityId($existing->getId());
        $row->setState($state);
        $row->setInsertDate(LocalDateTime::now());
        $this->identityRequestTable->create($row);

        $authorizationUri = Url::parse($existing->getAuthorizationUri());
        $authorizationUri = $authorizationUri->withParameters([
            'response_type' => 'code',
            'client_id' => $existing->getClientId(),
            'state' => $state,
            'redirect_uri' => $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch') . 'consumer/identity/' . $existing->getId() . '/exchange',
        ]);

        return $provider->getRedirectUri($authorizationUri);
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

        if (!$identityRequest instanceof Table\Generated\IdentityRequestRow) {
            throw new StatusCode\BadRequestException('Provided identity state was not requested');
        }

        if (LocalDateTime::now()->plusHours(1)->isBefore($identityRequest->getInsertDate())) {
            throw new StatusCode\BadRequestException('Provided identity request is expired');
        }

        $provider = $this->userProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        $configuration = new Configuration($existing->getClientId(), $existing->getClientSecret(), $existing->getAuthorizationUri(), $existing->getTokenUri(), $existing->getUserInfoUri(), $existing->getIdProperty(), $existing->getNameProperty(), $existing->getEmailProperty());

        $user = $provider->requestUserInfo($configuration, $code, $redirectUri);
        if (!$user instanceof UserInfo) {
            throw new StatusCode\BadRequestException('Could not request user information');
        }

        $userId = $this->userService->createRemote($existing->getId(), $user->getId(), $user->getName(), $user->getEmail(), UserContext::newAnonymousContext());

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
}
