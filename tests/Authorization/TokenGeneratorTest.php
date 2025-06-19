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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Authorization\TokenGenerator;
use PHPUnit\Framework\TestCase;

/**
 * TokenGeneratorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenGeneratorTest extends TestCase
{
    public function testGenerateRefreshToken()
    {
        $this->assertEquals(80, strlen(TokenGenerator::generateRefreshToken()));
    }

    public function testGenerateCode()
    {
        $this->assertEquals(16, strlen(TokenGenerator::generateCode()));
    }

    public function testGenerateAppKey()
    {
        $this->assertEquals(36, strlen(TokenGenerator::generateAppKey()));
    }

    public function testGenerateAppSecret()
    {
        $this->assertEquals(64, strlen(TokenGenerator::generateAppSecret()));
    }

    public function testGenerateUserPassword()
    {
        $this->assertEquals(20, strlen(TokenGenerator::generateUserPassword()));
    }
}
