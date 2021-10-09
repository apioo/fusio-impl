<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Service\App;

use Firebase\JWT\JWT;
use Fusio\Impl\Service\App\Token;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Oauth2\AccessToken;

/**
 * TokenTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class TokenTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGenerateAccessToken()
    {
        /** @var Token $tokenService */
        $tokenService = Environment::getService('app_token_service');
        $projectKey   = Environment::getConfig()->get('fusio_project_key');

        $token = $tokenService->generateAccessToken(1, 1, ['foo', 'bar'], '127.0.0.1', new \DateInterval('P1D'));

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals('bearer', $token->getTokenType());
        $this->assertNotEmpty($token->getAccessToken());
        $this->assertNotEmpty($token->getExpiresIn());
        $this->assertNotEmpty($token->getRefreshToken());

        $jwt = JWT::decode($token->getAccessToken(), $projectKey, ['HS256']);

        $this->assertEquals('http://127.0.0.1', $jwt->iss);
        $this->assertEquals('b2493ea4-c99b-5cc9-8004-4fdbe90f674b', $jwt->sub);
        $this->assertEquals('Administrator', $jwt->name);
    }
}
