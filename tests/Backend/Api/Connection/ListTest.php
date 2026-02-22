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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Impl\Tests\DbTestCase;

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
        $response = $this->sendRequest('/backend/connection/list', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "connections": [
        {
            "name": "AMQP",
            "class": "Fusio.Adapter.Amqp.Connection.Amqp"
        },
        {
            "name": "Agent",
            "class": "Fusio.Adapter.Ai.Connection.Agent"
        },
        {
            "name": "Agent-Connection",
            "class": "Fusio.Impl.Tests.Adapter.Test.AgentConnection"
        },
        {
            "name": "Beanstalk",
            "class": "Fusio.Adapter.Beanstalk.Connection.Beanstalk"
        },
        {
            "name": "Filesystem",
            "class": "Fusio.Adapter.File.Connection.Filesystem"
        },
        {
            "name": "GraphQL",
            "class": "Fusio.Adapter.GraphQL.Connection.GraphQL"
        },
        {
            "name": "HTTP",
            "class": "Fusio.Adapter.Http.Connection.Http"
        },
        {
            "name": "Paypal-Connection",
            "class": "Fusio.Impl.Tests.Adapter.Test.PaypalConnection"
        },
        {
            "name": "Redis",
            "class": "Fusio.Adapter.Redis.Connection.Redis"
        },
        {
            "name": "SDK-Airtable",
            "class": "Fusio.Adapter.SdkFabric.Connection.Airtable"
        },
        {
            "name": "SDK-Discord",
            "class": "Fusio.Adapter.SdkFabric.Connection.Discord"
        },
        {
            "name": "SDK-Notion",
            "class": "Fusio.Adapter.SdkFabric.Connection.Notion"
        },
        {
            "name": "SDK-OpenAI",
            "class": "Fusio.Adapter.SdkFabric.Connection.OpenAI"
        },
        {
            "name": "SDK-Starwars",
            "class": "Fusio.Adapter.SdkFabric.Connection.Starwars"
        },
        {
            "name": "SDK-Twitter",
            "class": "Fusio.Adapter.SdkFabric.Connection.Twitter"
        },
        {
            "name": "SMTP",
            "class": "Fusio.Adapter.Smtp.Connection.Smtp"
        },
        {
            "name": "SOAP",
            "class": "Fusio.Adapter.Soap.Connection.Soap"
        },
        {
            "name": "SQL",
            "class": "Fusio.Adapter.Sql.Connection.Sql"
        },
        {
            "name": "SQL-Advanced",
            "class": "Fusio.Adapter.Sql.Connection.SqlAdvanced"
        },
        {
            "name": "Stripe",
            "class": "Fusio.Adapter.Stripe.Connection.Stripe"
        },
        {
            "name": "Void-Connection",
            "class": "Fusio.Impl.Tests.Adapter.Test.VoidConnection"
        },
        {
            "name": "Worker",
            "class": "Fusio.Adapter.Worker.Connection.Worker"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
