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

namespace Fusio\Impl\Authorization;

use PSX\Framework\Util\Uuid;

/**
 * TokenGenerator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenGenerator
{
    /**
     * Generates the bearer authorization token
     */
    public static function generateToken(): string
    {
        return implode('-', [
            self::generateString(20),
            self::generateString(48),
            self::generateString(10)
        ]);
    }

    /**
     * Generates the authorization code
     */
    public static function generateCode(): string
    {
        return self::generateString(16);
    }

    /**
     * Generates a random state for OAuth2 authorization
     */
    public static function generateState(): string
    {
        return self::generateString(32);
    }

    /**
     * Generates the app key
     */
    public static function generateAppKey(): string
    {
        return Uuid::pseudoRandom();
    }

    /**
     * Generates the app secret
     */
    public static function generateAppSecret(): string
    {
        return self::generateString(64);
    }

    /**
     * Generates the user password
     */
    public static function generateUserPassword(): string
    {
        return self::generateString(20);
    }

    private static function generateString(int $length): string
    {
        if ($length <= 0) {
            throw new \RuntimeException('Length must be positive');
        }

        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
