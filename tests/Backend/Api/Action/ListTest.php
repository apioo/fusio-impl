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

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ListTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ListTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/action/list', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "actions": [
        {
            "name": "CLI-Processor",
            "class": "CliProcessor"
        },
        {
            "name": "FastCGI-Processor",
            "class": "FcgiProcessor"
        },
        {
            "name": "File-Directory-Get",
            "class": "FileDirectoryGet"
        },
        {
            "name": "File-Directory-GetAll",
            "class": "FileDirectoryGetAll"
        },
        {
            "name": "File-Processor",
            "class": "FileProcessor"
        },
        {
            "name": "GraphQL-Processor",
            "class": "GraphQLProcessor"
        },
        {
            "name": "HTTP-Composition",
            "class": "HttpComposition"
        },
        {
            "name": "HTTP-Load-Balancer",
            "class": "HttpLoadBalancer"
        },
        {
            "name": "HTTP-Processor",
            "class": "HttpProcessor"
        },
        {
            "name": "Inspect-Action",
            "class": "InspectAction"
        },
        {
            "name": "PHP-Processor",
            "class": "PhpProcessor"
        },
        {
            "name": "PHP-Sandbox",
            "class": "PhpSandbox"
        },
        {
            "name": "SMTP-Send",
            "class": "SmtpSend"
        },
        {
            "name": "SQL-Builder",
            "class": "SqlBuilder"
        },
        {
            "name": "SQL-Delete",
            "class": "SqlDelete"
        },
        {
            "name": "SQL-Insert",
            "class": "SqlInsert"
        },
        {
            "name": "SQL-Query-All",
            "class": "SqlQueryAll"
        },
        {
            "name": "SQL-Query-Row",
            "class": "SqlQueryRow"
        },
        {
            "name": "SQL-Select-All",
            "class": "SqlSelectAll"
        },
        {
            "name": "SQL-Select-Row",
            "class": "SqlSelectRow"
        },
        {
            "name": "SQL-Update",
            "class": "SqlUpdate"
        },
        {
            "name": "Util-A\/B-Test",
            "class": "UtilABTest"
        },
        {
            "name": "Util-Cache",
            "class": "UtilCache"
        },
        {
            "name": "Util-Dispatch-Event",
            "class": "UtilDispatchEvent"
        },
        {
            "name": "Util-JSON-Patch",
            "class": "UtilJsonPatch"
        },
        {
            "name": "Util-Redirect",
            "class": "UtilRedirect"
        },
        {
            "name": "Util-Static-Response",
            "class": "UtilStaticResponse"
        },
        {
            "name": "Void-Action",
            "class": "VoidAction"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
