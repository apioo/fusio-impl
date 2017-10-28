<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Connection\System;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Service;
use Fusio\Impl\Tests\Adapter\Test\InspectAction;
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

        $version = \Fusio\Impl\Database\Installer::getLatestVersion();
        $dataSet = array_merge_recursive($version->getInstallInserts(), self::getTestInserts());

        return self::$dataSet = new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet($dataSet);
    }

    protected static function getTestInserts()
    {
        $schemaEntrySource = self::getEntrySchema();
        $schemaCollectionSource = self::getCollectionSchema();

        $parser = new JsonSchema();
        $schemaEntry = $parser->parse($schemaEntrySource);

        $parser = new JsonSchema();
        $schemaCollection = $parser->parse($schemaCollectionSource);

        $expire = new \DateTime();
        $expire->add(new \DateInterval('P1M'));

        $secretKey = '42eec18ffdbffc9fda6110dcc705d6ce';

        return [
            'fusio_user' => [
                ['status' => 0, 'name' => 'Consumer', 'email' => 'consumer@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'date' => '2015-02-27 19:59:15'],
                ['status' => 2, 'name' => 'Disabled', 'email' => 'disabled@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Developer', 'email' => 'developer@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'date' => '2015-02-27 19:59:15'],
                ['status' => 3, 'name' => 'Deleted', 'email' => 'deleted@localhost.com', 'password' => '$2y$10$8EZyVlUy.oNrF8NcDxY7OeTBt6.3fikdH82JlfeRhqSlXitxJMdB6', 'date' => '2015-02-27 19:59:15'],
            ],
            'fusio_action' => [
                ['status' => 1, 'name' => 'Util-Static-Response', 'class' => UtilStaticResponse::class, 'engine' => PhpClass::class, 'config' => serialize(['response' => '{"foo": "bar"}']), 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Sql-Table', 'class' => SqlTable::class, 'engine' => PhpClass::class, 'config' => serialize(['connection' => 1, 'table' => 'app_news']), 'date' => '2015-02-27 19:59:15'],
                ['status' => 1, 'name' => 'Inspect-Action', 'class' => InspectAction::class, 'engine' => PhpClass::class, 'config' => serialize([]), 'date' => '2015-02-27 19:59:15'],
            ],
            'fusio_app' => [
                ['userId' => 2, 'status' => 1, 'name' => 'Foo-App', 'url' => 'http://google.com', 'parameters' => '', 'appKey' => '5347307d-d801-4075-9aaa-a21a29a448c5', 'appSecret' => '342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d', 'date' => '2015-02-22 22:19:07'],
                ['userId' => 2, 'status' => 2, 'name' => 'Pending', 'url' => 'http://google.com', 'parameters' => '', 'appKey' => '7c14809c-544b-43bd-9002-23e1c2de6067', 'appSecret' => 'bb0574181eb4a1326374779fe33e90e2c427f28ab0fc1ffd168bfd5309ee7caa', 'date' => '2015-02-22 22:19:07'],
                ['userId' => 2, 'status' => 3, 'name' => 'Deactivated', 'url' => 'http://google.com', 'parameters' => '', 'appKey' => 'f46af464-f7eb-4d04-8661-13063a30826b', 'appSecret' => '17b882987298831a3af9c852f9cd0219d349ba61fcf3fc655ac0f07eece951f9', 'date' => '2015-02-22 22:19:07'],
            ],
            'fusio_app_code' => [
                ['appId' => 3, 'userId' => 3, 'code' => 'GHMbtJi0ZuAUnp80', 'redirectUri' => '', 'scope' => 'authorization', 'date' => date('Y-m-d H:i:s')],
            ],
            'fusio_audit' => [
                ['appId' => 1, 'userId' => 1, 'refId' => 1, 'event' => 'app.update', 'ip' => '127.0.0.1', 'message' => 'Created schema foo', 'content' => null, 'date' => '2015-06-25 22:49:09'],
            ],
            'fusio_connection' => [
                ['status' => 1, 'name' => 'System', 'class' => System::class, 'config' => Service\Connection::encryptConfig(['foo' => 'bar'], $secretKey)],
            ],
            'fusio_cronjob' => [
                ['status' => 1, 'name' => 'Test-Cron', 'cron' => '*/30 * * * *', 'action' => 3, 'executeDate' => '2015-02-27 19:59:15', 'exitCode' => 0],
            ],
            'fusio_cronjob_error' => [
                ['cronjobId' => 1, 'message' => 'Syntax error, malformed JSON', 'trace' => '[trace]', 'file' => '[file]', 'line' => 74],
            ],
            'fusio_deploy_migration' => [
                ['connection' => 'Default-Connection', 'file' => 'resources/sql/v4_schema.php', 'fileHash' => 'db8b19c8da5872ca683510944b27db5fbbd011bb', 'executeDate' => '2017-04-30 17:15:42'],
            ],
            'fusio_routes' => [
                ['status' => 1, 'methods' => 'ANY', 'path' => '/foo', 'controller' => SchemaApiController::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/inspect/:foo', 'controller' => SchemaApiController::class],
            ],
            'fusio_rate' => [
                ['status' => 1, 'priority' => 5, 'name' => 'silver', 'rateLimit' => 8, 'timespan' => 'P1M'],
                ['status' => 1, 'priority' => 10, 'name' => 'gold', 'rateLimit' => 16, 'timespan' => 'P1M'],
            ],
            'fusio_rate_allocation' => [
                ['rateId' => 3, 'routeId' => self::getLastRouteId() + 1, 'appId' => null, 'authenticated' => null, 'parameters' => null],
                ['rateId' => 4, 'routeId' => self::getLastRouteId() + 1, 'appId' => null, 'authenticated' => 1, 'parameters' => null],
            ],
            'fusio_routes_method' => [
                ['routeId' => self::getLastRouteId() + 1, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => null, 'action' => 3],
                ['routeId' => self::getLastRouteId() + 1, 'method' => 'POST', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 0, 'parameters' => null, 'request' => 3, 'action' => 3],
                ['routeId' => self::getLastRouteId() + 1, 'method' => 'PUT', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'parameters' => null, 'request' => null, 'action' => null],
                ['routeId' => self::getLastRouteId() + 1, 'method' => 'PATCH', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'parameters' => null, 'request' => null, 'action' => null],
                ['routeId' => self::getLastRouteId() + 1, 'method' => 'DELETE','version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 0, 'public' => 0, 'parameters' => null, 'request' => null, 'action' => null],

                ['routeId' => self::getLastRouteId() + 2, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => 1, 'action' => 4],
                ['routeId' => self::getLastRouteId() + 2, 'method' => 'POST', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => 1, 'action' => 4],
                ['routeId' => self::getLastRouteId() + 2, 'method' => 'PUT', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => 1, 'action' => 4],
                ['routeId' => self::getLastRouteId() + 2, 'method' => 'PATCH', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => 1, 'action' => 4],
                ['routeId' => self::getLastRouteId() + 2, 'method' => 'DELETE','version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => 1, 'action' => 4],
            ],
            'fusio_routes_response' => [
                ['methodId' => 2, 'code' => 200, 'response' => 2],
                ['methodId' => 3, 'code' => 201, 'response' => 1],

                ['methodId' => 7, 'code' => 200, 'response' => 1],
                ['methodId' => 8, 'code' => 200, 'response' => 1],
                ['methodId' => 9, 'code' => 200, 'response' => 1],
                ['methodId' => 10, 'code' => 200, 'response' => 1],
                ['methodId' => 11, 'code' => 200, 'response' => 1],
            ],
            'fusio_log' => [
                ['appId' => 3, 'routeId' => 1, 'ip' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36', 'method' => 'GET', 'path' => '/bar', 'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'body' => 'foobar', 'executionTime' => 500000, 'date' => '2015-06-25 22:49:09'],
                ['appId' => 3, 'routeId' => 1, 'ip' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.130 Safari/537.36', 'method' => 'GET', 'path' => '/bar', 'header' => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8', 'body' => 'foobar', 'executionTime' => 500000, 'date' => '2015-06-25 22:49:09'],
            ],
            'fusio_log_error' => [
                ['logId' => 1, 'message' => 'Syntax error, malformed JSON', 'trace' => '[trace]', 'file' => '[file]', 'line' => 74],
            ],
            'fusio_schema' => [
                ['status' => 1, 'name' => 'Collection-Schema', 'source' => $schemaCollectionSource, 'cache' => serialize($schemaCollection)],
                ['status' => 1, 'name' => 'Entry-Schema', 'source' => $schemaEntrySource, 'cache' => serialize($schemaEntry)],
            ],
            'fusio_scope' => [
                ['name' => 'foo', 'description' => 'Foo access'],
                ['name' => 'bar', 'description' => 'Bar access'],
            ],
            'fusio_app_scope' => [
                ['appId' => 3, 'scopeId' => 3],
                ['appId' => 3, 'scopeId' => 4],
                ['appId' => 3, 'scopeId' => 5],
            ],
            'fusio_app_token' => [
                ['appId' => 1, 'userId' => 1, 'status' => 1, 'token' => 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'refresh' => '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'scope' => 'backend,authorization', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
                ['appId' => 2, 'userId' => 1, 'status' => 1, 'token' => 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'refresh' => 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'scope' => 'consumer,authorization', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
                ['appId' => 3, 'userId' => 2, 'status' => 1, 'token' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'refresh' => 'b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2', 'scope' => 'bar', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
                ['appId' => 1, 'userId' => 4, 'status' => 1, 'token' => 'e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f', 'refresh' => 'da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf', 'scope' => 'backend', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
                ['appId' => 2, 'userId' => 2, 'status' => 1, 'token' => '1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b', 'refresh' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873', 'scope' => 'consumer', 'ip' => '127.0.0.1', 'expire' => $expire->format('Y-m-d H:i:s'), 'date' => '2015-06-25 22:49:09'],
            ],
            'fusio_scope_routes' => [
                ['scopeId' => 5, 'routeId' => self::getLastRouteId(), 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
                ['scopeId' => 5, 'routeId' => self::getLastRouteId() + 1, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
                ['scopeId' => 5, 'routeId' => self::getLastRouteId() + 2, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'],
            ],
            'fusio_user_scope' => [
                ['userId' => 1, 'scopeId' => 4],
                ['userId' => 1, 'scopeId' => 5],
                ['userId' => 2, 'scopeId' => 2],
                ['userId' => 2, 'scopeId' => 3],
                ['userId' => 2, 'scopeId' => 4],
                ['userId' => 2, 'scopeId' => 5],
                ['userId' => 3, 'scopeId' => 3],
                ['userId' => 4, 'scopeId' => 1],
                ['userId' => 4, 'scopeId' => 2],
                ['userId' => 4, 'scopeId' => 3],
            ],
            'fusio_user_grant' => [
                ['userId' => 1, 'appId' => 1, 'allow' => 1, 'date' => '2015-02-27 19:59:15'],
            ],
            'app_news' => [
                ['id' => 1, 'title' => 'foo', 'content' => 'bar', 'date' => '2015-02-27 19:59:15'],
                ['id' => 2, 'title' => 'bar', 'content' => 'foo', 'date' => '2015-02-27 19:59:15'],
            ],
        ];
    }

    private static function getEntrySchema()
    {
        return <<<'JSON'
{
    "title": "entry",
    "type": "object",
    "properties": {
        "id": {
            "type": "integer"
        },
        "title": {
            "type": "string"
        },
        "content": {
            "type": "string"
        },
        "date": {
            "type": "string",
            "format": "date-time"
        }
    }
}
JSON;
    }

    private static function getCollectionSchema()
    {
        return <<<'JSON'
{
    "title": "collection",
    "type": "object",
    "properties": {
        "totalResults": {
            "type": "integer"
        },
        "itemsPerPage": {
            "type": "integer"
        },
        "startIndex": {
            "type": "integer"
        },
        "entry": {
            "type": "array",
            "items": {
                "title": "entry",
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "title": {
                        "type": "string"
                    },
                    "content": {
                        "type": "string"
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            }
        }
    }
}
JSON;
    }

    public static function getLastRouteId()
    {
        static $routeId;

        if ($routeId) {
            return $routeId;
        }

        $version = \Fusio\Impl\Database\Installer::getLatestVersion();
        $data    = $version->getInstallInserts();
        $routeId = count($data['fusio_routes']);

        return $routeId;
    }
}
