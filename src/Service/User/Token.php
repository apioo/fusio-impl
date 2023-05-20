<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\User;

use Firebase\JWT\JWT;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
    public function getUser(string $token): int
    {
        try {
            $this->jsonWebToken->decode($token);
        } catch (\RuntimeException $e) {
            throw new StatusCode\BadRequestException('Invalid token provided');
        }

        $user = $this->userTable->findOneByToken($token);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Could not find user for token');
        }

        $this->resetToken($user);

        return $user->getId();
    }

    /**
     * Generates a one time token for the user and assigns the token to the user
     */
    public function generateToken(int $userId): string
    {
        $existing = $this->userTable->find($userId);
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
