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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Consumer\TokenCreate;
use Fusio\Model\Consumer\TokenUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token
{
    private Service\Token $tokenService;
    private Service\Config $configService;
    private Table\Token $tokenTable;
    private Table\App $appTable;
    private Table\Scope $scopeTable;

    public function __construct(Service\Token $tokenService, Service\Config $configService, Table\Token $tokenTable, Table\App $appTable, Table\Scope $scopeTable)
    {
        $this->tokenService = $tokenService;
        $this->configService = $configService;
        $this->tokenTable = $tokenTable;
        $this->appTable = $appTable;
        $this->scopeTable = $scopeTable;
    }

    public function create(TokenCreate $token, UserContext $context): AccessToken
    {
        $this->assertMaxTokenCount($context);

        $rawScopes = $token->getScope() ?? [];
        $rawScopes[] = 'authorization'; // automatically add the authorization scope which a user can not select

        $scopes = $this->scopeTable->getValidUserScopes($context->getTenantId(), $context->getUserId(), $rawScopes);
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the token');
        }

        $expire = $token->getExpire();
        if (empty($expire)) {
            throw new StatusCode\BadRequestException('No expire date provided');
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $name = $token->getName();
        if (empty($name)) {
            $name = 'Personal-Access-Token by ' . $userAgent . ' (' . $ip . ')';;
        }

        return $this->tokenService->generate(
            $context->getTenantId(),
            null,
            $context->getUserId(),
            $name,
            $scopes,
            $ip,
            $expire->toDateTime()
        );
    }

    public function update(string $tokenId, TokenUpdate $token, UserContext $context): AccessToken
    {
        $existing = $this->tokenTable->findOneByTenantAndId($context->getTenantId(), (int) $tokenId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find token');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Token does not belong to the user');
        }

        $expire = $token->getExpire();
        if (empty($expire)) {
            throw new StatusCode\BadRequestException('No expire date provided');
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $name = $token->getName();
        if (empty($name)) {
            $name = 'Personal-Access-Token by ' . $userAgent . ' (' . $ip . ')';;
        }

        return $this->tokenService->refresh(
            $context->getTenantId(),
            $name,
            $existing->getRefresh(),
            $ip,
            $expire->toDateTime(),
            new \DateInterval('P1Y2M')
        );
    }

    public function delete(string $tokenId, UserContext $context): void
    {
        $existing = $this->tokenTable->findOneByTenantAndId($context->getTenantId(), (int) $tokenId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find token');
        }

        if ($existing->getUserId() != $context->getUserId()) {
            throw new StatusCode\BadRequestException('Token does not belong to the user');
        }

        $this->tokenService->remove($existing->getId(), $context);
    }

    private function assertMaxTokenCount(UserContext $context): void
    {
        $count = $this->tokenTable->getCountForUser($context->getTenantId(), $context->getUserId());
        if ($count > $this->configService->getValue('consumer_max_tokens')) {
            throw new StatusCode\BadRequestException('Maximal amount of tokens reached. Please delete another token in order to generate a new one');
        }
    }
}
