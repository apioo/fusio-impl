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

namespace Fusio\Impl\Tests;

use Fusio\Adapter\Sql\Action\SqlInsert;
use Fusio\Adapter\Sql\Action\SqlSelectAll;
use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Impl\Connection\Native;
use Fusio\Impl\Installation\DataBag;
use Fusio\Impl\Installation\NewInstallation;
use Fusio\Impl\Installation\Operation;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Adapter\Test\InspectAction;
use Fusio\Impl\Tests\Adapter\Test\PaypalConnection;
use PSX\Api\OperationInterface;

/**
 * Fixture
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Fixture
{
    private static ?DataBag $data = null;

    public static function getDataSet(): array
    {
        return self::getData()->toArray();
    }

    public static function getData(): DataBag
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $data = NewInstallation::getData();
        self::appendTestInserts($data);

        return self::$data = $data;
    }

    public static function getId(string $table, string $name): ?int
    {
        return self::getData()->getId($table, $name);
    }

    private static function appendTestInserts(DataBag $data)
    {
        $schemaEntrySource = file_get_contents(__DIR__ . '/resources/entry_schema.json');
        $schemaEntryForm = file_get_contents(__DIR__ . '/resources/entry_form.json');
        $schemaCollectionSource = file_get_contents(__DIR__ . '/resources/collection_schema.json');

        $secretKey = '42eec18ffdbffc9fda6110dcc705d6ce';

        $data->addPlan('Plan A', 3999, 500, ProductInterface::INTERVAL_SUBSCRIPTION, 'price_1L3dOA2Tb35ankTn36cCgliu', ['foo' => 'bar']);
        $data->addPlan('Plan B', 4999, 1000, null);
        $data->addUser('Consumer', 'Consumer', 'consumer@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 100, Table\User::STATUS_ACTIVE, 'Plan A', ['foo' => 'bar']);
        $data->addUser('Consumer', 'Disabled', 'disabled@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', null, Table\User::STATUS_DISABLED);
        $data->addUser('Backend', 'Developer', 'developer@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 10, Table\User::STATUS_ACTIVE);
        $data->addUser('Backend', 'Deleted', 'deleted@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', null, Table\User::STATUS_DELETED);
        $data->addAction('default', 'Util-Static-Response', UtilStaticResponse::class, Service\Action::serializeConfig(['response' => '{"foo": "bar"}']), ['foo' => 'bar']);
        $data->addAction('default', 'Sql-Select-All', SqlSelectAll::class, Service\Action::serializeConfig(['connection' => 2, 'table' => 'app_news']));
        $data->addAction('default', 'Sql-Insert', SqlInsert::class, Service\Action::serializeConfig(['connection' => 2, 'table' => 'app_news']));
        $data->addAction('default', 'Inspect-Action', InspectAction::class);
        $data->addApp('Consumer', 'Foo-App', 'http://google.com', '5347307d-d801-4075-9aaa-a21a29a448c5', '342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d', Table\App::STATUS_ACTIVE, ['foo' => 'bar']);
        $data->addApp('Consumer', 'Pending', 'http://google.com', '7c14809c-544b-43bd-9002-23e1c2de6067', 'bb0574181eb4a1326374779fe33e90e2c427f28ab0fc1ffd168bfd5309ee7caa', Table\App::STATUS_PENDING);
        $data->addApp('Consumer', 'Deactivated', 'http://google.com', 'f46af464-f7eb-4d04-8661-13063a30826b', '17b882987298831a3af9c852f9cd0219d349ba61fcf3fc655ac0f07eece951f9', Table\App::STATUS_DEACTIVATED);
        $data->addAppCode('Foo-App', 'Developer', 'GHMbtJi0ZuAUnp80', 'authorization');
        $data->addAudit('Backend', 'Administrator', 1, 'app.update', 'Created schema foo', '2015-06-25 22:49:09');
        $data->addConnection('Test', Native::class, Service\Connection\Encrypter::encrypt(['foo' => 'bar'], $secretKey), ['foo' => 'bar']);
        $data->addConnection('paypal', PaypalConnection::class, Service\Connection\Encrypter::encrypt(['foo' => 'bar'], $secretKey));
        $data->addCronjob('default', 'Test-Cron', '* * * * *', 'Sql-Select-All', ['foo' => 'bar']);
        $data->addCronjobError('Test-Cron', 'Syntax error, malformed JSON');
        $data->addEvent('default', 'foo-event', 'Foo event description', ['foo' => 'bar']);
        $data->addEventSubscription('foo-event', 'Administrator', 'http://www.fusio-project.org/ping');
        $data->addEventSubscription('foo-event', 'Consumer', 'http://www.fusio-project.org/ping');
        $data->addEventTrigger('foo-event', '{"foo":"bar"}', '2018-06-02 14:24:30');
        $data->addEventResponse(0, 0);
        $data->addRate('silver', 5, 8, 'P1M', ['foo' => 'bar']);
        $data->addRate('gold', 10, 16, 'P1M');
        $data->addTransaction('Administrator', 'Plan B', 3999, 'last month', 'next month', '2018-10-05 18:18:00');
        $data->addSchema('default', 'Collection-Schema', $schemaCollectionSource, null, ['foo' => 'bar']);
        $data->addSchema('default', 'Entry-Schema', $schemaEntrySource, $schemaEntryForm);
        $data->addScope('default', 'foo', 'Foo access', ['foo' => 'bar']);
        $data->addScope('default', 'bar', 'Bar access');
        $data->addScope('default', 'plan_scope', 'Plan scope access');
        $data->addAppScope('Foo-App', 'authorization');
        $data->addAppScope('Foo-App', 'foo');
        $data->addAppScope('Foo-App', 'bar');
        $data->addAppToken('Backend', 'Administrator', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'backend,authorization', '+1 month');
        $data->addAppToken('Consumer', 'Administrator', 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'consumer,authorization', '+1 month');
        $data->addAppToken('Foo-App', 'Consumer', 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'bar', '+1 month', '2015-06-25 22:49:09');
        $data->addAppToken('Foo-App', 'Developer', 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'bar', '+1 month');
        $data->addAppToken('Consumer', 'Consumer', '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'consumer', '+1 month');
        $data->addAppToken('Backend', 'Developer', 'bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'backend', '+1 month');
        $data->addPlanScope('Plan A', 'foo');
        $data->addPlanScope('Plan A', 'bar');
        $data->addPlanScope('Plan A', 'plan_scope');
        $data->addUserScope('Administrator', 'foo');
        $data->addUserScope('Administrator', 'bar');
        $data->addUserScope('Consumer', 'consumer');
        $data->addUserScope('Consumer', 'authorization');
        $data->addUserScope('Consumer', 'foo');
        $data->addUserScope('Consumer', 'bar');
        $data->addUserScope('Disabled', 'authorization');
        $data->addUserScope('Developer', 'backend');
        $data->addUserScope('Developer', 'consumer');
        $data->addUserScope('Developer', 'authorization');
        $data->addUserScope('Developer', 'foo');
        $data->addUserScope('Developer', 'bar');
        $data->addUserGrant('Administrator', 'Backend', true, '2015-02-27 19:59:15');

        $data->addOperations('default', [
            'test.listFoo' => new Operation(
                action: 'Sql-Select-All',
                httpMethod: 'GET',
                httpPath: '/foo',
                httpCode: 200,
                outgoing: 'Collection-Schema',
                public: true,
                stability: OperationInterface::STABILITY_EXPERIMENTAL,
            ),
            'test.createFoo' => new Operation(
                action: 'Sql-Insert',
                httpMethod: 'POST',
                httpPath: '/foo',
                httpCode: 201,
                outgoing: 'Passthru',
                incoming: 'Entry-Schema',
                costs: 1,
            ),
            'inspect.get' => new Operation(
                action: 'Inspect-Action',
                httpMethod: 'GET',
                httpPath: '/inspect/:foo',
                httpCode: 200,
                outgoing: 'Passthru',
                incoming: 'Passthru',
            ),
            'inspect.post' => new Operation(
                action: 'Inspect-Action',
                httpMethod: 'POST',
                httpPath: '/inspect/:foo',
                httpCode: 200,
                outgoing: 'Passthru',
                incoming: 'Passthru',
            ),
            'inspect.put' => new Operation(
                action: 'Inspect-Action',
                httpMethod: 'PUT',
                httpPath: '/inspect/:foo',
                httpCode: 200,
                outgoing: 'Passthru',
                incoming: 'Passthru',
            ),
            'inspect.patch' => new Operation(
                action: 'Inspect-Action',
                httpMethod: 'PATCH',
                httpPath: '/inspect/:foo',
                httpCode: 200,
                outgoing: 'Passthru',
                incoming: 'Passthru',
            ),
            'inspect.delete' => new Operation(
                action: 'Inspect-Action',
                httpMethod: 'DELETE',
                httpPath: '/inspect/:foo',
                httpCode: 200,
                outgoing: 'Passthru',
                incoming: 'Passthru',
            ),
        ]);

        $data->addLog('default', 'Foo-App', 'test.listFoo');
        $data->addLog('default', 'Foo-App', 'test.listFoo');
        $data->addLogError(0);
        $data->addPlanUsage('test.listFoo', 'Administrator', 'Foo-App', 1, '2018-10-05 18:18:00');
        $data->addRateAllocation('silver', 'test.listFoo');
        $data->addRateAllocation('gold', 'test.createFoo', null, null, null, true);
        $data->addScopeOperation('bar', 'test.listFoo');
        $data->addScopeOperation('bar', 'test.createFoo');
        $data->addScopeOperation('foo', 'inspect.get');
        $data->addScopeOperation('bar', 'inspect.get');
        $data->addScopeOperation('foo', 'inspect.post');
        $data->addScopeOperation('bar', 'inspect.post');
        $data->addScopeOperation('bar', 'inspect.put');
        $data->addScopeOperation('bar', 'inspect.patch');
        $data->addScopeOperation('bar', 'inspect.delete');

        $data->addTable('app_news', [
            ['title' => 'foo', 'content' => 'bar', 'date' => '2015-02-27 19:59:15'],
            ['title' => 'bar', 'content' => 'foo', 'date' => '2015-02-27 19:59:15'],
        ]);
    }
}
