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

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ListTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ListTest extends DbTestCase
{
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
            "name": "AMQP-Publish",
            "class": "Fusio.Adapter.Amqp.Action.AmqpPublish"
        },
        {
            "name": "Beanstalk-Publish",
            "class": "Fusio.Adapter.Beanstalk.Action.BeanstalkPublish"
        },
        {
            "name": "CLI-Processor",
            "class": "Fusio.Adapter.Cli.Action.CliProcessor"
        },
        {
            "name": "FastCGI-Processor",
            "class": "Fusio.Adapter.Fcgi.Action.FcgiProcessor"
        },
        {
            "name": "File-Directory-Get",
            "class": "Fusio.Adapter.File.Action.FileDirectoryGet"
        },
        {
            "name": "File-Directory-GetAll",
            "class": "Fusio.Adapter.File.Action.FileDirectoryGetAll"
        },
        {
            "name": "File-Processor",
            "class": "Fusio.Adapter.File.Action.FileProcessor"
        },
        {
            "name": "GraphQL-Processor",
            "class": "Fusio.Adapter.GraphQL.Action.GraphQLProcessor"
        },
        {
            "name": "HTTP-Composition",
            "class": "Fusio.Adapter.Http.Action.HttpComposition"
        },
        {
            "name": "HTTP-Load-Balancer",
            "class": "Fusio.Adapter.Http.Action.HttpLoadBalancer"
        },
        {
            "name": "HTTP-Processor",
            "class": "Fusio.Adapter.Http.Action.HttpProcessor"
        },
        {
            "name": "HTTP-Raw",
            "class": "Fusio.Adapter.Http.Action.HttpRaw"
        },
        {
            "name": "Inspect-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.InspectAction"
        },
        {
            "name": "PHP-Processor",
            "class": "Fusio.Adapter.Php.Action.PhpProcessor"
        },
        {
            "name": "PHP-Sandbox",
            "class": "Fusio.Adapter.Php.Action.PhpSandbox"
        },
        {
            "name": "Redis-Hash-Delete",
            "class": "Fusio.Adapter.Redis.Action.RedisHashDelete"
        },
        {
            "name": "Redis-Hash-Get",
            "class": "Fusio.Adapter.Redis.Action.RedisHashGet"
        },
        {
            "name": "Redis-Hash-GetAll",
            "class": "Fusio.Adapter.Redis.Action.RedisHashGetAll"
        },
        {
            "name": "Redis-Hash-Set",
            "class": "Fusio.Adapter.Redis.Action.RedisHashSet"
        },
        {
            "name": "SMTP-Send",
            "class": "Fusio.Adapter.Smtp.Action.SmtpSend"
        },
        {
            "name": "SQL-Builder",
            "class": "Fusio.Adapter.Sql.Action.SqlBuilder"
        },
        {
            "name": "SQL-Delete",
            "class": "Fusio.Adapter.Sql.Action.SqlDelete"
        },
        {
            "name": "SQL-Insert",
            "class": "Fusio.Adapter.Sql.Action.SqlInsert"
        },
        {
            "name": "SQL-Query-All",
            "class": "Fusio.Adapter.Sql.Action.Query.SqlQueryAll"
        },
        {
            "name": "SQL-Query-Row",
            "class": "Fusio.Adapter.Sql.Action.Query.SqlQueryRow"
        },
        {
            "name": "SQL-Select-All",
            "class": "Fusio.Adapter.Sql.Action.SqlSelectAll"
        },
        {
            "name": "SQL-Select-Row",
            "class": "Fusio.Adapter.Sql.Action.SqlSelectRow"
        },
        {
            "name": "SQL-Update",
            "class": "Fusio.Adapter.Sql.Action.SqlUpdate"
        },
        {
            "name": "Util-A\/B-Test",
            "class": "Fusio.Adapter.Util.Action.UtilABTest"
        },
        {
            "name": "Util-Cache",
            "class": "Fusio.Adapter.Util.Action.UtilCache"
        },
        {
            "name": "Util-Chain",
            "class": "Fusio.Adapter.Util.Action.UtilChain"
        },
        {
            "name": "Util-Condition",
            "class": "Fusio.Adapter.Util.Action.UtilCondition"
        },
        {
            "name": "Util-Dispatch-Event",
            "class": "Fusio.Adapter.Util.Action.UtilDispatchEvent"
        },
        {
            "name": "Util-JSON-Patch",
            "class": "Fusio.Adapter.Util.Action.UtilJsonPatch"
        },
        {
            "name": "Util-Redirect",
            "class": "Fusio.Adapter.Util.Action.UtilRedirect"
        },
        {
            "name": "Util-Static-Response",
            "class": "Fusio.Adapter.Util.Action.UtilStaticResponse"
        },
        {
            "name": "Void-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.VoidAction"
        },
        {
            "name": "Worker-Java",
            "class": "Fusio.Adapter.Worker.Action.WorkerJava"
        },
        {
            "name": "Worker-Javascript",
            "class": "Fusio.Adapter.Worker.Action.WorkerJavascript"
        },
        {
            "name": "Worker-PHP",
            "class": "Fusio.Adapter.Worker.Action.WorkerPHP"
        },
        {
            "name": "Worker-PHP-Local",
            "class": "Fusio.Adapter.Worker.Action.WorkerPHPLocal"
        },
        {
            "name": "Worker-Python",
            "class": "Fusio.Adapter.Worker.Action.WorkerPython"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
