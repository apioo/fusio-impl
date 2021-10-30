<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Cronjob;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/cronjob/5', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/entity.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/cronjob/5', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 5,
    "status": 1,
    "name": "Test-Cron",
    "cron": "*\/30 * * * *",
    "action": "Sql-Select-All",
    "executeDate": "2015-02-27T19:59:15Z",
    "exitCode": 0,
    "errors": [
        {
            "message": "Syntax error, malformed JSON",
            "trace": "[trace]",
            "file": "[file]",
            "line": 74
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/cronjob/~Test-Cron', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 5,
    "status": 1,
    "name": "Test-Cron",
    "cron": "*\/30 * * * *",
    "action": "Sql-Select-All",
    "executeDate": "2015-02-27T19:59:15Z",
    "exitCode": 0,
    "errors": [
        {
            "message": "Syntax error, malformed JSON",
            "trace": "[trace]",
            "file": "[file]",
            "line": 74
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/cronjob/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find cronjob"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/cronjob/5', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        CronFile::reset();

        $response = $this->sendRequest('/backend/cronjob/5', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name' => 'Foo-Cron',
            'cron' => '10 * * * *',
            'action' => 'Inspect',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Cronjob successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'name', 'cron', 'action')
            ->from('fusio_cronjob')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => 5]);

        $this->assertEquals(5, $row['id']);
        $this->assertEquals('Foo-Cron', $row['name']);
        $this->assertEquals('10 * * * *', $row['cron']);
        $this->assertEquals('Inspect', $row['action']);

        // check generated cron file
        $actual = CronFile::get();
        $actual = preg_replace('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/m', '[date]', $actual);

        $cronExec = Environment::getService('config')->get('fusio_cron_exec');

        $expect = <<<CRON
# Generated by Fusio on [date]
# Do not edit this file manually since Fusio will overwrite those
# entries on generation.

10 * * * * {$cronExec} cronjob 5
0 * * * * {$cronExec} cronjob 4
0 0 * * * {$cronExec} cronjob 3
* * * * * {$cronExec} cronjob 2
* * * * * {$cronExec} cronjob 1


CRON;

        Assert::assertEqualsIgnoreWhitespace($expect, $actual);
    }

    public function testDelete()
    {
        CronFile::reset();

        $response = $this->sendRequest('/backend/cronjob/5', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Cronjob successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_cronjob')
            ->where('id = 5')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(5, $row['id']);
        $this->assertEquals(Table\Cronjob::STATUS_DELETED, $row['status']);

        // check generated cron file
        $actual = CronFile::get();
        $actual = preg_replace('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/m', '[date]', $actual);

        $cronExec = Environment::getService('config')->get('fusio_cron_exec');

        $expect = <<<CRON
# Generated by Fusio on [date]
# Do not edit this file manually since Fusio will overwrite those
# entries on generation.

0 * * * * {$cronExec} cronjob 4
0 0 * * * {$cronExec} cronjob 3
* * * * * {$cronExec} cronjob 2
* * * * * {$cronExec} cronjob 1


CRON;

        Assert::assertEqualsIgnoreWhitespace($expect, $actual);
    }
}
