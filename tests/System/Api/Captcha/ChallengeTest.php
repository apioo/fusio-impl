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

namespace Fusio\Impl\Tests\System\Api\Payment;

use Fusio\Impl\Service\Captcha;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Model\Backend\SchemaSource;
use Fusio\Model\System\CaptchaChallenge;
use PSX\Framework\Test\Environment;
use PSX\Schema\ObjectMapperInterface;

/**
 * ChallengeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ChallengeTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/system/captcha/challenge', 'GET', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);

        // solve captcha
        $objectMapper = Environment::getService(ObjectMapperInterface::class);
        $challenge = $objectMapper->readJson($body, \PSX\Schema\SchemaSource::fromClass(CaptchaChallenge::class));

        $captcha = Environment::getService(Captcha::class);
        $base64EncodedCaptcha = $captcha->solve($challenge);

        self::assertTrue($captcha->verify($base64EncodedCaptcha));
    }

    public function testGetExceedRateLimit(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $response = $this->sendRequest('/system/captcha/challenge', 'GET', [
                'User-Agent' => 'Fusio TestCase',
            ]);

            $body = (string) $response->getBody();

            $this->assertEquals($i < 15 ? 200 : 429, $response->getStatusCode(), $body);
        }
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/system/captcha/challenge', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/system/captcha/challenge', 'PUT', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/system/captcha/challenge', 'DELETE', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
