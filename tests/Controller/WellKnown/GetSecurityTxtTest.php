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

namespace Fusio\Impl\Tests\System\Api\WellKnown;

use Fusio\Impl\Tests\DbTestCase;

/**
 * GetSecurityTxtTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetSecurityTxtTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/.well-known/security.txt', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $expires = (new \DateTime())->add(new \DateInterval('P1M'))->format('Y-m-d\T00:00:00.000\Z');

        $actual = (string) $response->getBody();
        $expect = 'Contact: mailto:security@fusio-project.org' . "\n";
        $expect.= 'Contact: https://github.com/apioo/fusio' . "\n";
        $expect.= 'Contact: https://chrisk.app/' . "\n";
        $expect.= 'Expires: ' . $expires . "\n";
        $expect.= 'Encryption: https://chrisk.app/pub.key' . "\n";
        $expect.= 'Preferred-Languages: en';

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertEquals('text/plain', $response->getHeader('Content-Type'), $actual);
        $this->assertEquals($expect, $actual, $actual);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/.well-known/security.txt', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/.well-known/security.txt', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/.well-known/security.txt', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
