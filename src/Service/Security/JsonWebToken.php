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

namespace Fusio\Impl\Service\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Fusio\Impl\Service\System\FrameworkConfig;
use UnexpectedValueException;

/**
 * JsonWebToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonWebToken
{
    public const ALG = 'HS256';

    private FrameworkConfig $frameworkConfig;

    public function __construct(FrameworkConfig $frameworkConfig)
    {
        $this->frameworkConfig = $frameworkConfig;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->frameworkConfig->getProjectKey(), self::ALG);
    }

    /**
     * @throws UnexpectedValueException
     */
    public function decode(string $jwt): \stdClass
    {
        return JWT::decode($jwt, new Key($this->frameworkConfig->getProjectKey(), self::ALG));
    }
}
