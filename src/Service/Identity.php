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

use Fusio\Engine\Identity\ProviderInterface;
use Fusio\Engine\Identity\UserInfo;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Identity\CreatedEvent;
use Fusio\Impl\Event\Identity\DeletedEvent;
use Fusio\Impl\Event\Identity\UpdatedEvent;
use Fusio\Impl\Provider\IdentityProvider;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
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
    private Table\App $appTable;
    private Identity\Validator $validator;
    private IdentityProvider $identityProvider;
    private Service\User $userService;
    private Service\Token $tokenService;
    private Service\System\FrameworkConfig $frameworkConfig;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Identity $identityTable, Table\Generated\IdentityRequestTable $identityRequestTable, Table\App $appTable, Identity\Validator $validator, IdentityProvider $identityProvider, Service\User $userService, Service\Token $tokenService, Service\System\FrameworkConfig $frameworkConfig, EventDispatcherInterface $eventDispatcher)
    {
        $this->identityTable = $identityTable;
        $this->identityRequestTable = $identityRequestTable;
        $this->appTable = $appTable;
        $this->validator = $validator;
        $this->identityProvider = $identityProvider;
        $this->userService = $userService;
        $this->tokenService = $tokenService;
        $this->frameworkConfig = $frameworkConfig;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Model\Backend\IdentityCreate $identity, UserContext $context): int
    {
        $this->validator->assert($identity, $context->getTenantId());

        $provider = $this->identityProvider->getInstance($identity->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        try {
            $this->identityTable->beginTransaction();

            $config = $identity->getConfig() ? $identity->getConfig()->getAll() : [];

            // create category
            $row = new Table\Generated\IdentityRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Identity::STATUS_ACTIVE);
            $row->setAppId($identity->getAppId());
            $row->setRoleId($identity->getRoleId());
            $row->setName($identity->getName());
            $row->setIcon($identity->getIcon());
            $row->setClass(ClassName::serialize($identity->getClass()));
            $row->setConfig(self::serializeConfig($config));
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
        $existing = $this->identityTable->findOneByIdentifier($context->getTenantId(), $identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $this->validator->assert($identity, $context->getTenantId(), $existing);

        $provider = $this->identityProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        try {
            $this->identityTable->beginTransaction();

            $config = $identity->getConfig()?->getAll() ?? self::unserializeConfig($existing->getConfig());

            // update category
            $existing->setAppId($identity->getAppId() ?? $existing->getAppId());
            $existing->setRoleId($identity->getRoleId() ?? $existing->getRoleId());
            $existing->setName($identity->getName() ?? $existing->getName());
            $existing->setIcon($identity->getIcon() ?? $existing->getIcon());
            $existing->setConfig(self::serializeConfig($config));
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
        $existing = $this->identityTable->findOneByIdentifier($context->getTenantId(), $identityId);
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

    public function redirect(string $identityId, ?string $redirectUri, UserContext $context): Uri
    {
        $existing = $this->identityTable->findOneByIdentifier($context->getTenantId(), $identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $app = $this->appTable->find($existing->getAppId());
        if (!$app instanceof Table\Generated\AppRow) {
            throw new StatusCode\InternalServerErrorException('Configured entity is not assigned to an app');
        }

        if (!empty($redirectUri)) {
            $redirectUrl = Url::parse($redirectUri);
            $appUrl = Url::parse($app->getUrl());
            if ($redirectUrl->getHost() !== $appUrl->getHost()) {
                throw new StatusCode\BadRequestException('Provided redirect url must have the same host as the configured app url');
            }
        }

        $provider = $this->identityProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        $state = TokenGenerator::generateState();

        $row = new Table\Generated\IdentityRequestRow();
        $row->setIdentityId($existing->getId());
        $row->setState($state);
        $row->setRedirectUri($redirectUri);
        $row->setInsertDate(LocalDateTime::now());
        $this->identityRequestTable->create($row);

        $config = self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config);

        return $provider->getRedirectUri($parameters, $state, $this->buildRedirectUri($existing));
    }

    public function exchange(string $identityId, string $code, string $state, UserContext $context): AccessToken
    {
        $existing = $this->identityTable->findOneByIdentifier($context->getTenantId(), $identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\IdentityRequestTable::COLUMN_IDENTITY_ID, $existing->getId());
        $condition->equals(Table\Generated\IdentityRequestTable::COLUMN_STATE, $state);
        $identityRequest = $this->identityRequestTable->findOneBy($condition);

        if (!$identityRequest instanceof Table\Generated\IdentityRequestRow) {
            throw new StatusCode\BadRequestException('Provided identity state was not requested');
        }

        if (LocalDateTime::now()->plusHours(1)->isBefore($identityRequest->getInsertDate())) {
            throw new StatusCode\BadRequestException('Provided identity request is expired');
        }

        $provider = $this->identityProvider->getInstance($existing->getClass());
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provided an invalid identity class');
        }

        $config = self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config);

        $user = $provider->requestUserInfo($parameters, $code, $this->buildRedirectUri($existing));
        if (!$user instanceof UserInfo) {
            throw new StatusCode\BadRequestException('Could not request user information');
        }

        $userId = $this->userService->createRemote($existing, $user, UserContext::newAnonymousContext());

        // get scopes for user
        $scopes = $this->userService->getAvailableScopes($userId, $context);

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $name = 'Identity Provider ' . $existing->getName() . ' by ' . $userAgent . ' (' . $ip . ')';

        $accessToken = $this->tokenService->generate(
            $context->getTenantId(),
            null,
            $userId,
            $name,
            $scopes,
            $ip,
            $this->frameworkConfig->getExpireTokenInterval()
        );

        $redirectUri = $identityRequest->getRedirectUri();
        if (!empty($redirectUri)) {
            // redirect the user in case a redirect uri was provided
            $url = Url::parse($redirectUri);
            $url = $url->withParameters(array_merge($url->getParameters(), [
                'access_token' => $accessToken->getAccessToken(),
                'token_type' => $accessToken->getTokenType(),
                'expires_in' => $accessToken->getExpiresIn(),
                'refresh_token' => $accessToken->getRefreshToken(),
                'scope' => $accessToken->getScope(),
            ]));

            throw new StatusCode\FoundException($url->toString());
        }

        return $accessToken;
    }

    private function buildRedirectUri(Table\Generated\IdentityRow $existing): string
    {
        return $this->frameworkConfig->getDispatchUrl('consumer', 'identity', $existing->getId(), 'exchange');
    }

    public static function serializeConfig(?array $config = null): ?string
    {
        if (empty($config)) {
            return null;
        }

        return \json_encode($config);
    }

    public static function unserializeConfig(?string $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        return \json_decode($data, true);
    }
}
