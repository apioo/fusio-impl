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

namespace Fusio\Impl\Tests\Backend\Api\Action;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ListTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
            "class": "Fusio\\Adapter\\Cli\\Action\\CliProcessor"
        },
        {
            "name": "Cli-Engine",
            "class": "Fusio\\Adapter\\Cli\\Action\\CliEngine"
        },
        {
            "name": "FastCGI-Processor",
            "class": "Fusio\\Adapter\\Fcgi\\Action\\FcgiProcessor"
        },
        {
            "name": "Fcgi-Engine",
            "class": "Fusio\\Adapter\\Fcgi\\Action\\FcgiEngine"
        },
        {
            "name": "File-Directory-Detail",
            "class": "Fusio\\Adapter\\File\\Action\\FileDirectoryDetail"
        },
        {
            "name": "File-Directory-Index",
            "class": "Fusio\\Adapter\\File\\Action\\FileDirectoryIndex"
        },
        {
            "name": "File-Engine",
            "class": "Fusio\\Adapter\\File\\Action\\FileEngine"
        },
        {
            "name": "File-Processor",
            "class": "Fusio\\Adapter\\File\\Action\\FileProcessor"
        },
        {
            "name": "GraphQL-Processor",
            "class": "Fusio\\Adapter\\GraphQL\\Action\\GraphQLProcessor"
        },
        {
            "name": "HTTP-Composition",
            "class": "Fusio\\Adapter\\Http\\Action\\HttpComposition"
        },
        {
            "name": "HTTP-Load-Balancer",
            "class": "Fusio\\Adapter\\Http\\Action\\HttpLoadBalancer"
        },
        {
            "name": "HTTP-Processor",
            "class": "Fusio\\Adapter\\Http\\Action\\HttpProcessor"
        },
        {
            "name": "Http-Engine",
            "class": "Fusio\\Adapter\\Http\\Action\\HttpEngine"
        },
        {
            "name": "Inspect-Action",
            "class": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction"
        },
        {
            "name": "PHP-Processor",
            "class": "Fusio\\Adapter\\Php\\Action\\PhpProcessor"
        },
        {
            "name": "PHP-Sandbox",
            "class": "Fusio\\Adapter\\Php\\Action\\PhpSandbox"
        },
        {
            "name": "Php-Engine",
            "class": "Fusio\\Adapter\\Php\\Action\\PhpEngine"
        },
        {
            "name": "SMTP-Send",
            "class": "Fusio\\Adapter\\Smtp\\Action\\SmtpSend"
        },
        {
            "name": "SQL-Builder",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlBuilder"
        },
        {
            "name": "SQL-Delete",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlDelete"
        },
        {
            "name": "SQL-Insert",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlInsert"
        },
        {
            "name": "SQL-Query-All",
            "class": "Fusio\\Adapter\\Sql\\Action\\Query\\SqlQueryAll"
        },
        {
            "name": "SQL-Query-Row",
            "class": "Fusio\\Adapter\\Sql\\Action\\Query\\SqlQueryRow"
        },
        {
            "name": "SQL-Select-All",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlSelectAll"
        },
        {
            "name": "SQL-Select-Row",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlSelectRow"
        },
        {
            "name": "SQL-Update",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlUpdate"
        },
        {
            "name": "Util-A\/B-Test",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilABTest"
        },
        {
            "name": "Util-Cache",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilCache"
        },
        {
            "name": "Util-Dispatch-Event",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilDispatchEvent"
        },
        {
            "name": "Util-JSON-Patch",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilJsonPatch"
        },
        {
            "name": "Util-Redirect",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilRedirect"
        },
        {
            "name": "Util-Static-Response",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse"
        },
        {
            "name": "Void-Action",
            "class": "Fusio\\Impl\\Tests\\Adapter\\Test\\VoidAction"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
