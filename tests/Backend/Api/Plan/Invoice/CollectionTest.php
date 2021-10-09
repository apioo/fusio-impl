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

namespace Fusio\Impl\Tests\Backend\Api\Plan\Invoice;

use Fusio\Impl\Table;
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
        $response = $this->sendRequest('/system/doc/*/backend/plan/invoice', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/collection.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/plan/invoice', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 2,
            "contractId": 1,
            "user": {
                "id": 1,
                "name": "Administrator"
            },
            "prevId": 1,
            "displayId": "0001-2019-897635",
            "status": 0,
            "amount": 19.99,
            "points": 100,
            "fromDate": "2019-04-27T00:00:00Z",
            "toDate": "2019-04-27T00:00:00Z",
            "insertDate": "2019-04-27T20:57:00Z"
        },
        {
            "id": 1,
            "contractId": 1,
            "user": {
                "id": 1,
                "name": "Administrator"
            },
            "displayId": "0001-2019-896280",
            "status": 1,
            "amount": 19.99,
            "points": 100,
            "fromDate": "2019-04-27T00:00:00Z",
            "toDate": "2019-04-27T00:00:00Z",
            "payDate": "2019-04-27T20:57:00Z",
            "insertDate": "2019-04-27T20:57:00Z"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/plan/invoice', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'contractId' => 1,
            'startDate'  => '2019-05-01T00:00:00',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Invoice successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'contract_id', 'prev_id', 'status', 'amount', 'points', 'from_date', 'to_date', 'pay_date')
            ->from('fusio_plan_invoice')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['id']);
        $this->assertEquals(1, $row['contract_id']);
        $this->assertNull($row['prev_id']);
        $this->assertEquals(Table\Plan\Invoice::STATUS_OPEN, $row['status']);
        $this->assertEquals(19.99, $row['amount']);
        $this->assertEquals(50, $row['points']);
        $this->assertEquals('2019-05-01', $row['from_date']);
        $this->assertEquals('2019-06-01', $row['to_date']);
        $this->assertNull($row['pay_date']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/plan/invoice', 'PUT', array(
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
        $response = $this->sendRequest('/backend/plan/invoice', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
