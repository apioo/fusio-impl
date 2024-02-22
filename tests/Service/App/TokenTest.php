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

namespace Fusio\Impl\Tests\Service\App;

use Firebase\JWT\JWT;
use Fusio\Impl\Service\App\Token;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Oauth2\AccessToken;

/**
 * TokenTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGenerateAccessToken()
    {
        /** @var Token $tokenService */
        $tokenService = Environment::getService(Token::class);
        $token = $tokenService->generateAccessToken(null, 1, 1, ['foo', 'bar'], '127.0.0.1', new \DateInterval('P1D'));

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals('bearer', $token->getTokenType());
        $this->assertNotEmpty($token->getAccessToken());
        $this->assertNotEmpty($token->getExpiresIn());
        $this->assertNotEmpty($token->getRefreshToken());

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $jwt = $jsonWebToken->decode($token->getAccessToken());

        $this->assertEquals('http://127.0.0.1', $jwt->iss);
        $this->assertEquals('b2493ea4-c99b-5cc9-8004-4fdbe90f674b', $jwt->sub);
        $this->assertEquals('Administrator', $jwt->name);
    }
}
