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
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Connection\Native;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Service;
use Fusio\Impl\Table\Plan\Invoice;
use Fusio\Impl\Tests\Adapter\Test\InspectAction;
use Fusio\Impl\Tests\Adapter\Test\PaypalConnection;
use PSX\Api\Resource;
use PSX\Schema\Parser\JsonSchema;

/**
 * Fixture
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Fixture
{
    protected static $dataSet;

    public static function getDataSet()
    {
        if (self::$dataSet !== null) {
            return self::$dataSet;
        }

        $dataSet = array_merge_recursive(
            NewInstallation::getData(),
            self::getTestInserts()
        );

        return self::$dataSet = $dataSet;
    }

    protected static function getTestInserts()
    {
        $schemaEntrySource = file_get_contents(__DIR__ . '/resources/entry_schema.json');
        $schemaEntryForm = file_get_contents(__DIR__ . '/resources/entry_form.json');
        $schemaCollectionSource = file_get_contents(__DIR__ . '/resources/collection_schema.json');

        $schemaEntry = (new JsonSchema())->parse($schemaEntrySource);
        $schemaCollection = (new JsonSchema())->parse($schemaCollectionSource);

        $now = new \DateTime();
        $expire = new \DateTime();
        $expire->add(new \DateInterval('P1M'));

        $secretKey = '42eec18ffdbffc9fda6110dcc705d6ce';

        return [
            'fusio_user' => [
                ['status' => 0, 'name' => 'Consumer', 'email' => 'consumer@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'points' => 100, 'date' => '2015-02-27 19:59:15'],
                ['status' => 2, 'name' => 'Disabled', 'email' => 'disabled@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'points' => null, 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Developer', 'email' => 'developer@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'points' => 10, 'date' => '2015-02-27 19:59:15'],
                ['status' => 3, 'name' => 'Deleted', 'email' => 'deleted@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'points' => null, 'date' => '2015-02-27 19:59:15'],
            ],
            'fusio_action' => [
                ['status' => 1, 'name' => 'Util-Static-Response', 'class' => UtilStaticResponse::class, 'engine' => PhpClass::class, 'config' => Service\Action::serializeConfig(['response' => '{"foo": "bar"}']), 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Sql-Table', 'class' => SqlTable::class, 'engine' => PhpClass::class, 'config' => Service\Action::serializeConfig(['connection' => 2, 'table' => 'app_news']), 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Inspect-Action', 'class' => InspectAction::class, 'engine' => PhpClass::class, 'config' => Service\Action::serializeConfig([]), 'date' => '2015-02-27 19:59:15'],
            ],
            'fusio_app' => [
                ['user_id' => 2, 'status' => 1, 'name' => 'Foo-App', 'url' => 'http://google.com', 'parameters' => '', 'app_key' => '5347307d-d801-4075-9aaa-a21a29a448c5', 'app_secret' => '342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d', 'date' => '2015-02-22 22:19:07'],
                ['user_id' => 2, 'status' => 2, 'name' => 'Pending', 'url' => 'http://google.com', 'parameters' => '', 'app_key' => '7c14809c-544b-43bd-9002-23e1c2de6067', 'app_secret' => 'bb0574181eb4a1326374779fe33e90e2c427f28ab0fc1ffd168bfd5309ee7caa', 'date' => '2015-02-22 22:19:07'],
                ['user_id' => 2, 'status' => 3, 'name' => 'Deactivated', 'url' => 'http://google.com', 'parameters' => '', 'app_key' => 'f46af464-f7eb-4d04-8661-13063a30826b', 'app_secret' => '17b882987298831a3af9c852f9cd0219d349ba61fcf3fc655ac0f07eece951f9', 'date' => '2015-02-22 22:19:07'],
            ],
            'fusio_app_code' => [
                ['app_id' => 3, 'user_id' => 4, 'code' => 'GHMbtJi0ZuAUnp80', 'redirect_uri' => '', 'scope' => 'authorization', 'date' => date('Y-m-d H:i:s')],
            ],
            'fusio_audit' => [
                ['app_id' => 1, 'user_id' => 1, 'ref_id' => 1, 'event' => 'app.update', 'ip' => '127.0.0.1', 'message' => 'Created schema foo', 'content' => null, 'date' => '2015-06-25 22:49:09'],
            ],
            'fusio_connection' => [
                ['status' => 1, 'name' => 'Test', 'class' => Native::class, 'config' => Service\Connection::encryptConfig(['foo' => 'bar'], $secretKey)],
                ['status' => 1, 'name' => 'paypal', 'class' => PaypalConnection::class, 'config' => Service\Connection::encryptConfig(['foo' => 'bar'], $secretKey)],
            ],
            'fusio_cronjob' => [
                ['status' => 1, 'name' => 'Test-Cron', 'cron' => '*/30 * * * *', 'action' => 3, 'execute_date' => '2015-02-27 19:59:15', 'exit_code' => 0],
            ],
            'fusio_cronjob_error' => [
                ['cronjob_id' => 1, 'message' => 'Syntax error, malformed JSON', 'trace' => '[trace]', 'file' => '[file]', 'line' => 74],
            ],
            'fusio_event' => [
                ['status' => 1, 'name' => 'foo-event', 'description' => 'Foo event description'],
            ],
            'fusio_event_subscription' => [
                ['event_id' => 37, 'user_id' => 1, 'status' => 1, 'endpoint' => 'http://www.fusio-project.org/ping'],
                ['event_id' => 37, 'user_id' => 2, 'status' => 1, 'endpoint' => 'http://www.fusio-project.org/ping'],
            ],
            'fusio_event_trigger' => [
                ['event_id' => 37, 'status' => 2, 'payload' => '{"foo":"bar"}', 'insert_date' => '2018-06-02 14:24:30'],
            ],
            'fusio_event_response' => [
                ['trigger_id' => 1, 'subscription_id' => 1, 'status' => 2, 'code' => 200, 'attempts' => 1, 'execute_date' => '2018-06-02 14:41:23', 'insert_date' => '2018-06-02 14:41:23'],
            ],
            'fusio_routes' => [
                ['status' => 1, 'priority' => 1, 'methods' => 'ANY', 'path' => '/foo', 'controller' => SchemaApiController::class],
                ['status' => 1, 'priority' => 2, 'methods' => 'ANY', 'path' => '/inspect/:foo', 'controller' => SchemaApiController::class],
            ],
            'fusio_rate' => [
                ['status' => 1, 'priority' => 5, 'name' => 'silver', 'rate_limit' => 8, 'timespan' => 'P1M'],
                ['status' => 1, 'priority' => 10, 'name' => 'gold', 'rate_limit' => 16, 'timespan' => 'P1M'],
            ],
            'fusio_rate_allocation' => [
                ['rate_id' => 3, 'route_id' => self::getLastRouteId() + 1, 'app_id' => null, 'authenticated' => null, 'parameters' => null],
                ['rate_id' => 4, 'route_id' => self::getLastRouteId() + 1, 'app_id' => null, 'authenticated' => 1, 'parameters' => null],
            ],
            'fusio_routes_response' => [
                ['method_id' => 2, 'code' => 200, 'response' => 2],
                ['method_id' => 3, 'code' => 201, 'response' => 1],

                ['method_id' => 7, 'code' => 200, 'response' => 1],
                ['method_id' => 8, 'code' => 200, 'response' => 1],
                ['method_id' => 9, 'code' => 200, 'response' => 1],
                ['method_id' => 10, 'code' => 200, 'response' => 1],
                ['method_id' => 11, 'code' => 200, 'response' => 1],
            ],
            'fusio_routes_method' => [
                ['route_id' => self::getLastRouteId() + 1, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => 'listFoo', 'parameters' => null, 'request' => null, 'action' => 3, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 1, 'method' => 'POST', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 0, 'operation_id' => 'createFoo', 'parameters' => null, 'request' => 3, 'action' => 3, 'costs' => 1],
                ['route_id' => self::getLastRouteId() + 1, 'method' => 'PUT', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'operation_id' => null, 'parameters' => null, 'request' => null, 'action' => null, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 1, 'method' => 'PATCH', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'operation_id' => null, 'parameters' => null, 'request' => null, 'action' => null, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 1, 'method' => 'DELETE','version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'operation_id' => null, 'parameters' => null, 'request' => null, 'action' => null, 'costs' => null],

                ['route_id' => self::getLastRouteId() + 2, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => null, 'parameters' => null, 'request' => 1, 'action' => 4, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 2, 'method' => 'POST', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => null, 'parameters' => null, 'request' => 1, 'action' => 4, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 2, 'method' => 'PUT', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => null, 'parameters' => null, 'request' => 1, 'action' => 4, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 2, 'method' => 'PATCH', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => null, 'parameters' => null, 'request' => 1, 'action' => 4, 'costs' => null],
                ['route_id' => self::getLastRouteId() + 2, 'method' => 'DELETE','version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'operation_id' => null, 'parameters' => null, 'request' => 1, 'action' => 4, 'costs' => null],
            ],
            'fusio_log' => [
                ['app_id' => 3, 'route_id' => 1, 'ip' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36', 'method' => 'GET', 'path' => '/bar', 'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'body' => 'foobar', 'execution_time' => 500000, 'date' => '2015-06-25 22:49:09'],
                ['app_id' => 3, 'route_id' => 1, 'ip' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36', 'method' => 'GET', 'path' => '/bar', 'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'body' => 'foobar', 'execution_time' => 500000, 'date' => '2015-06-25 22:49:09'],
            ],
            'fusio_log_error' => [
                ['log_id' => 1, 'message' => 'Syntax error, malformed JSON', 'trace' => '[trace]', 'file' => '[file]', 'line' => 74],
            ],
            'fusio_plan' => [
                ['status' => 1, 'name' => 'Plan A', 'description' => '', 'price' => 39.99, 'points' => 500, 'period_type' => 1],
                ['status' => 1, 'name' => 'Plan B', 'description' => '', 'price' => 49.99, 'points' => 1000, 'period_type' => null],
            ],
            'fusio_plan_contract' => [
                ['user_id' => 1, 'plan_id' => 1, 'status' => 1, 'amount' => 19.99, 'points' => 50, 'period_type' => 1, 'insert_date' => '2018-10-05 18:18:00'],
            ],
            'fusio_plan_invoice' => [
                ['contract_id' => 1, 'prev_id' => null, 'user_id' => 1, 'display_id' => '0001-2019-896280', 'status' => Invoice::STATUS_PAYED, 'amount' => 19.99, 'points' => 100, 'from_date' => '2019-04-27', 'to_date' => '2019-04-27', 'pay_date' => '2019-04-27 20:57:00', 'insert_date' => '2019-04-27 20:57:00'],
                ['contract_id' => 1, 'prev_id' => 1, 'user_id' => 1, 'display_id' => '0001-2019-897635', 'status' => Invoice::STATUS_OPEN, 'amount' => 19.99, 'points' => 100, 'from_date' => '2019-04-27', 'to_date' => '2019-04-27', 'pay_date' => null, 'insert_date' => '2019-04-27 20:57:00'],
            ],
            'fusio_plan_usage' => [
                ['route_id' => 1, 'user_id' => 1, 'app_id' => 1, 'points' => 1, 'insert_date' => '2018-10-05 18:18:00'],
            ],
            'fusio_transaction' => [
                ['invoice_id' => 2, 'status' => 1, 'provider' => 'paypal', 'transaction_id' => '9e239bb3-cfb4-4783-92e0-18ce187041bc', 'remote_id' => 'PAY-1B56960729604235TKQQIYVY', 'amount' => 39.99, 'return_url' => 'http://myapp.com', 'update_date' => null, 'insert_date' => '2018-10-05 18:18:00'],
            ],
            'fusio_schema' => [
                ['status' => 1, 'name' => 'Collection-Schema', 'source' => $schemaCollectionSource, 'cache' => Service\Schema::serializeCache($schemaCollection)],
                ['status' => 1, 'name' => 'Entry-Schema', 'source' => $schemaEntrySource, 'cache' => Service\Schema::serializeCache($schemaEntry), 'form' => $schemaEntryForm],
            ],
            'fusio_scope' => [
                ['name' => 'foo', 'description' => 'Foo access'],
                ['name' => 'bar', 'description' => 'Bar access'],
            ],
            'fusio_app_scope' => [
                ['app_id' => 3, 'scope_id' => 3],
                ['app_id' => 3, 'scope_id' => 33],
                ['app_id' => 3, 'scope_id' => 34],
            ],
            'fusio_app_token' => [
                ['app_id' => 1, 'user_id' => 1, 'status' => 1, 'token' => 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'refresh' => '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'scope' => 'backend,authorization', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => $now->format('Y-m-d H:i:s')],
                ['app_id' => 2, 'user_id' => 1, 'status' => 1, 'token' => 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'refresh' => 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'scope' => 'consumer,authorization', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => $now->format('Y-m-d H:i:s')],
                ['app_id' => 3, 'user_id' => 2, 'status' => 1, 'token' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'refresh' => 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'scope' => 'bar', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
                ['app_id' => 3, 'user_id' => 4, 'status' => 1, 'token' => 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'refresh' => 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'scope' => 'bar', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => $now->format('Y-m-d H:i:s')],
                ['app_id' => 2, 'user_id' => 2, 'status' => 1, 'token' => '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'refresh' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'scope' => 'consumer', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => $now->format('Y-m-d H:i:s')],
                ['app_id' => 1, 'user_id' => 4, 'status' => 1, 'token' => 'bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a', 'refresh' => 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'scope' => 'backend', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_scope_routes' => [
                ['scope_id' => 34, 'route_id' => self::getLastRouteId(), 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
                ['scope_id' => 34, 'route_id' => self::getLastRouteId() + 1, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
                ['scope_id' => 34, 'route_id' => self::getLastRouteId() + 2, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
            ],
            'fusio_user_scope' => [
                ['user_id' => 1, 'scope_id' => 33],
                ['user_id' => 1, 'scope_id' => 34],
                ['user_id' => 2, 'scope_id' => 2],
                ['user_id' => 2, 'scope_id' => 3],
                ['user_id' => 2, 'scope_id' => 33],
                ['user_id' => 2, 'scope_id' => 34],
                ['user_id' => 3, 'scope_id' => 3],
                ['user_id' => 4, 'scope_id' => 1],
                ['user_id' => 4, 'scope_id' => 2],
                ['user_id' => 4, 'scope_id' => 3],
                ['user_id' => 4, 'scope_id' => 33],
                ['user_id' => 4, 'scope_id' => 34],
            ],
            'fusio_user_grant' => [
                ['user_id' => 1, 'app_id' => 1, 'allow' => 1, 'date' => '2015-02-27 19:59:15'],
            ],
            'fusio_user_attribute' => [
                ['user_id' => 1, 'name' => 'first_name', 'value' => 'Johann'],
                ['user_id' => 1, 'name' => 'last_name', 'value' => 'Bach'],
            ],
            'app_news' => [
                ['title' => 'foo', 'content' => 'bar', 'date' => '2015-02-27 19:59:15'],
                ['title' => 'bar', 'content' => 'foo', 'date' => '2015-02-27 19:59:15'],
            ],
        ];
    }

    public static function getLastRouteId()
    {
        static $routeId;

        if ($routeId) {
            return $routeId;
        }

        $data    = NewInstallation::getData();
        $routeId = count($data['fusio_routes']);

        return $routeId;
    }
}
