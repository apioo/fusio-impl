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

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/cronjob', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/collection.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/cronjob', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 5,
            "status": 1,
            "name": "Test-Cron",
            "cron": "*\/30 * * * *",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/cronjob?search=Test', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 5,
            "status": 1,
            "name": "Test-Cron",
            "cron": "*\/30 * * * *",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/cronjob?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 5,
            "status": 1,
            "name": "Test-Cron",
            "cron": "*\/30 * * * *",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        CronFile::reset();

        $response = $this->sendRequest('/backend/cronjob', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name' => 'New-Cron',
            'cron' => '5 * * * *',
            'action' => 'Sql-Table',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Cronjob successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'name', 'cron', 'action')
            ->from('fusio_cronjob')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals('New-Cron', $row['name']);
        $this->assertEquals('5 * * * *', $row['cron']);
        $this->assertEquals('Sql-Table', $row['action']);

        // check generated cron file
        $actual = CronFile::get();
        $actual = preg_replace('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/m', '[date]', $actual);

        $cronExec = Environment::getService('config')->get('fusio_cron_exec');

        $expect = <<<CRON
# Generated by Fusio on [date]
# Do not edit this file manually since Fusio will overwrite those
# entries on generation.

5 * * * * {$cronExec} cronjob 6
*/30 * * * * {$cronExec} cronjob 5
0 * * * * {$cronExec} cronjob 4
0 0 * * * {$cronExec} cronjob 3
* * * * * {$cronExec} cronjob 2
* * * * * {$cronExec} cronjob 1


CRON;

        Assert::assertEqualsIgnoreWhitespace($expect, $actual);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/cronjob', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/cronjob', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
