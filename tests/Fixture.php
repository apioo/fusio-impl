<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests;

use Fusio\Adapter\Sql\Action\SqlTable;
use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Impl\Connection\Native;
use Fusio\Impl\Migrations\DataBag;
use Fusio\Impl\Migrations\Method;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Plan\Invoice;
use Fusio\Impl\Tests\Adapter\Test\InspectAction;
use Fusio\Impl\Tests\Adapter\Test\PaypalConnection;

/**
 * Fixture
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Fixture
{
    private static $data;

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

        $expire = new \DateTime();
        $expire->add(new \DateInterval('P1M'));

        $secretKey = '42eec18ffdbffc9fda6110dcc705d6ce';

        $data->addUser('Consumer', 'consumer@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 100, Table\User::STATUS_CONSUMER, '2015-02-27 19:59:15');
        $data->addUser('Disabled', 'disabled@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', null, Table\User::STATUS_DISABLED, '2015-02-27 19:59:15');
        $data->addUser('Developer', 'developer@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 10, Table\User::STATUS_ADMINISTRATOR, '2015-02-27 19:59:15');
        $data->addUser('Deleted', 'deleted@localhost.com', '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', null, Table\User::STATUS_DELETED, '2015-02-27 19:59:15');
        $data->addAction('default', 'Util-Static-Response', UtilStaticResponse::class, Service\Action::serializeConfig(['response' => '{"foo": "bar"}']), '2015-02-27 19:59:15');
        $data->addAction('default', 'Sql-Table', SqlTable::class, Service\Action::serializeConfig(['connection' => 2, 'table' => 'app_news']), '2015-02-27 19:59:15');
        $data->addAction('default', 'Inspect-Action', InspectAction::class, null, '2015-02-27 19:59:15');
        $data->addApp('Consumer', 'Foo-App', 'http://google.com', '5347307d-d801-4075-9aaa-a21a29a448c5', '342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d', Table\App::STATUS_ACTIVE, '2015-02-22 22:19:07');
        $data->addApp('Consumer', 'Pending', 'http://google.com', '7c14809c-544b-43bd-9002-23e1c2de6067', 'bb0574181eb4a1326374779fe33e90e2c427f28ab0fc1ffd168bfd5309ee7caa', Table\App::STATUS_PENDING, '2015-02-22 22:19:07');
        $data->addApp('Consumer', 'Deactivated', 'http://google.com', 'f46af464-f7eb-4d04-8661-13063a30826b', '17b882987298831a3af9c852f9cd0219d349ba61fcf3fc655ac0f07eece951f9', Table\App::STATUS_DEACTIVATED, '2015-02-22 22:19:07');
        $data->addAppCode('Foo-App', 'Developer', 'GHMbtJi0ZuAUnp80', 'authorization');
        $data->addAudit('Backend', 'Administrator', 1, 'app.update', 'Created schema foo', '2015-06-25 22:49:09');
        $data->addConnection('Test', Native::class, Service\Connection::encryptConfig(['foo' => 'bar'], $secretKey));
        $data->addConnection('paypal', PaypalConnection::class, Service\Connection::encryptConfig(['foo' => 'bar'], $secretKey));
        $data->addCronjob('Test-Cron', '*/30 * * * *', 'Sql-Table');
        $data->addCronjobError('Test-Cron', 'Syntax error, malformed JSON');
        $data->addEvent('default', 'foo-event', 'Foo event description');
        $data->addEventSubscription('foo-event', 'Administrator', 'http://www.fusio-project.org/ping');
        $data->addEventSubscription('foo-event', 'Consumer', 'http://www.fusio-project.org/ping');
        $data->addEventTrigger('foo-event', '{"foo":"bar"}', '2018-06-02 14:24:30');
        $data->addEventResponse(0, 0);
        $data->addRate('silver', 5, 8, 'P1M');
        $data->addRate('gold', 10, 16, 'P1M');
        $data->addPlan('Plan A', 39.99, 500, 1);
        $data->addPlan('Plan B', 49.99, 1000, null);
        $data->addPlanContract('Administrator', 'Plan A', 19.99, 50, 1, '2018-10-05 18:18:00');
        $data->addPlanContract('Administrator', 'Plan A', 19.99, 50, 1, '2018-10-05 18:18:00');
        $data->addPlanInvoice(0, null, 'Administrator', '0001-2019-896280', Invoice::STATUS_PAYED, 19.99, 100, '2019-04-27', '2019-04-27', '2019-04-27 20:57:00', '2019-04-27 20:57:00');
        $data->addPlanInvoice(0, 1, 'Administrator', '0001-2019-897635', Invoice::STATUS_OPEN, 19.99, 100, '2019-04-27', '2019-04-27', null, '2019-04-27 20:57:00');
        $data->addTransaction(0, 'paypal', '9e239bb3-cfb4-4783-92e0-18ce187041bc', 'PAY-1B56960729604235TKQQIYVY', 39.99, 'http://myapp.com', '2018-10-05 18:18:00');
        $data->addSchema('default', 'Collection-Schema', $schemaCollectionSource);
        $data->addSchema('default', 'Entry-Schema', $schemaEntrySource, $schemaEntryForm);
        $data->addScope('default', 'foo', 'Foo access');
        $data->addScope('default', 'bar', 'Bar access');
        $data->addAppScope('Foo-App', 'authorization');
        $data->addAppScope('Foo-App', 'foo');
        $data->addAppScope('Foo-App', 'bar');
        $data->addAppToken('Backend', 'Administrator', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'backend,authorization', $expire->format('Y-m-d H:i:s'));
        $data->addAppToken('Consumer', 'Administrator', 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'consumer,authorization', $expire->format('Y-m-d H:i:s'));
        $data->addAppToken('Foo-App', 'Consumer', 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'bar', $expire->format('Y-m-d H:i:s'), '2015-06-25 22:49:09');
        $data->addAppToken('Foo-App', 'Developer', 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'bar', $expire->format('Y-m-d H:i:s'));
        $data->addAppToken('Consumer', 'Consumer', '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'consumer', $expire->format('Y-m-d H:i:s'));
        $data->addAppToken('Backend', 'Developer', 'bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a', 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'backend', $expire->format('Y-m-d H:i:s'));
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
        $data->addUserAttribute('Administrator', 'first_name', 'Johann');
        $data->addUserAttribute('Administrator', 'last_name', 'Bach');

        $data->addRoutes('default', [
            '/foo' => [
                'GET' => new Method('Sql-Table', null, [200 => 'Collection-Schema']),
                'POST' => new Method('Sql-Table', 'Entry-Schema', [201 => 'Passthru']),
            ],
            '/inspect/:foo' => [
                'GET' => new Method('Inspect-Action', 'Passthru', [200 => 'Passthru']),
                'POST' => new Method('Inspect-Action', 'Passthru', [200 => 'Passthru']),
                'PUT' => new Method('Inspect-Action', 'Passthru', [200 => 'Passthru']),
                'DELETE' => new Method('Inspect-Action', 'Passthru', [200 => 'Passthru']),
                'PATCH' => new Method('Inspect-Action', 'Passthru', [200 => 'Passthru']),
            ]
        ]);

        $data->addLog('Foo-App', '/foo');
        $data->addLog('Foo-App', '/foo');
        $data->addLogError(0);
        $data->addPlanUsage('/foo', 'Administrator', 'Foo-App', 1, '2018-10-05 18:18:00');
        $data->addRateAllocation('silver', '/foo');
        $data->addRateAllocation('gold', '/foo', null, true);
        $data->addScopeRoute('foo', '/foo');
        $data->addScopeRoute('foo', '/inspect/:foo');
        $data->addScopeRoute('bar', '/foo');
        $data->addScopeRoute('bar', '/inspect/:foo');

        $data->addTable('app_news', [
            ['title' => 'foo', 'content' => 'bar', 'date' => '2015-02-27 19:59:15'],
            ['title' => 'bar', 'content' => 'foo', 'date' => '2015-02-27 19:59:15'],
        ]);
    }
}
