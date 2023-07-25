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

namespace Fusio\Impl\Tests\Backend\Api\Action;

use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Provider\Identity\Github;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * GetFormTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetFormTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/identity/form?class=' . ClassName::serialize(Github::class), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "input",
            "name": "client_id",
            "title": "Client-ID",
            "help": "Client-ID",
            "type": "text"
        },
        {
            "element": "input",
            "name": "client_secret",
            "title": "Client-Secret",
            "help": "Client-Secret",
            "type": "text"
        },
        {
            "element": "input",
            "name": "authorization_uri",
            "title": "Authorization-Uri",
            "help": "Client-Secret",
            "type": "text"
        },
        {
            "element": "input",
            "name": "token_uri",
            "title": "Token-Uri",
            "help": "Client-Secret",
            "type": "text"
        },
        {
            "element": "input",
            "name": "user_info_uri",
            "title": "User-Info-Uri",
            "help": "Client-Secret",
            "type": "text"
        },
        {
            "element": "input",
            "name": "id_property",
            "title": "ID-Property",
            "help": "Optional name of the id property from the user-info response",
            "type": "text"
        },
        {
            "element": "input",
            "name": "name_property",
            "title": "Name-Property",
            "help": "Optional name of the name property from the user-info response",
            "type": "text"
        },
        {
            "element": "input",
            "name": "email_property",
            "title": "Email-Property",
            "help": "Optional name of the email property from the user-info response",
            "type": "text"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
