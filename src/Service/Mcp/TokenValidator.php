<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Mcp\Server\Auth\TokenValidationResult;
use Mcp\Server\Auth\TokenValidatorInterface;

/**
 * TokenValidator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenValidator implements TokenValidatorInterface
{
    private ?int $userId = null;

    public function __construct(
        private readonly Table\Token $tokenTable,
        private readonly Table\User $userTable,
        private readonly Service\Security\JsonWebToken $jsonWebToken,
        private readonly Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function validate(string $token): TokenValidationResult
    {
        try {
            $this->jsonWebToken->decode($token);
        } catch (\Exception $e) {
            return new TokenValidationResult(false);
        }

        $accessToken = $this->tokenTable->findByAccessToken($this->frameworkConfig->getTenantId(), $token);
        if (empty($accessToken)) {
            return new TokenValidationResult(false);
        }

        $user = $this->userTable->find($accessToken['user_id']);
        if (!$user instanceof Table\Generated\UserRow || $user->getStatus() !== Table\User::STATUS_ACTIVE) {
            return new TokenValidationResult(false);
        }

        $this->userId = $user->getId();

        return new TokenValidationResult(true, [
            'sub' => $user->getId(),
            'scope' => $accessToken['scope'],
        ]);
    }

    public function getCurrentUserId(): ?int
    {
        return $this->userId;
    }
}
