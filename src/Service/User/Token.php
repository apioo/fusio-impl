<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token
{
    private Table\User $userTable;
    private JsonWebToken $jsonWebToken;

    public function __construct(Table\User $userTable, JsonWebToken $jsonWebToken)
    {
        $this->userTable = $userTable;
        $this->jsonWebToken = $jsonWebToken;
    }

    /**
     * Returns a user for the provided one time token. Note we delete the token in case we can return a valid user
     */
    public function getUser(?string $tenantId, string $token): int
    {
        try {
            $this->jsonWebToken->decode($token);
        } catch (\RuntimeException $e) {
            throw new StatusCode\BadRequestException('Invalid token provided');
        }

        $user = $this->userTable->findOneByTenantAndToken($tenantId, $token);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Could not find user for token');
        }

        $this->resetToken($user);

        return $user->getId();
    }

    /**
     * Generates a one time token for the user and assigns the token to the user
     */
    public function generateToken(?string $tenantId, int $userId): string
    {
        $existing = $this->userTable->findOneByTenantAndId($tenantId, $userId);
        if (!$existing instanceof Table\Generated\UserRow) {
            throw new \RuntimeException('Could not find provided user id');
        }

        $payload = [
            'exp' => time() + (60 * 60),
            'jti' => TokenGenerator::generateCode(),
        ];

        $token = $this->jsonWebToken->encode($payload);

        $existing->setToken($token);
        $this->userTable->update($existing);

        return $token;
    }

    /**
     * Removes any token from the provided user
     */
    public function resetToken(Table\Generated\UserRow $user): void
    {
        $user->setToken('');
        $this->userTable->update($user);
    }
}
