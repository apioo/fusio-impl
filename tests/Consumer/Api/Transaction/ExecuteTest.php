<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer\Api\Transaction;

use Fusio\Engine\Model\Transaction;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ExecuteTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/consumer/transaction/execute/9e239bb3-cfb4-4783-92e0-18ce187041bc', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/consumer\/transaction\/execute\/:transaction_id",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {}
    },
    "methods": {
        "GET": {
            "description": "Executes the payment on the remote provider and redirects the user to the app using the provided return url"
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/consumer\/transaction\/execute\/:transaction_id"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/consumer\/transaction\/execute\/:transaction_id"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/consumer\/transaction\/execute\/:transaction_id"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/transaction/execute/9e239bb3-cfb4-4783-92e0-18ce187041bc', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(307, $response->getStatusCode(), $body);
        $this->assertEquals('http://myapp.com', $response->getHeader('Location'), $body);

        // check whether the transaction was correct executed
        $transaction = $this->connection->fetchAssoc('SELECT invoice_id, status, remote_id FROM fusio_transaction WHERE transaction_id = :trans_id', ['trans_id' => '9e239bb3-cfb4-4783-92e0-18ce187041bc']);

        $this->assertEquals(Transaction::STATUS_APPROVED, $transaction['status']);
        $this->assertEquals('PAY-1B56960729604235TKQQIYVY', $transaction['remote_id']);

        $invoice = $this->connection->fetchAssoc('SELECT contract_id, status, pay_date FROM fusio_plan_invoice WHERE id = :id', ['id' => $transaction['invoice_id']]);

        $this->assertEquals(Table\Plan\Invoice::STATUS_PAYED, $invoice['status']);
        $this->assertNotEmpty($invoice['pay_date']);

        $contract = $this->connection->fetchAssoc('SELECT user_id, status FROM fusio_plan_contract WHERE id = :id', ['id' => $invoice['contract_id']]);

        $this->assertEquals(Table\Plan\Contract::STATUS_ACTIVE, $contract['status']);

        $user = $this->connection->fetchAssoc('SELECT points FROM fusio_user WHERE id = :id', ['id' => $contract['user_id']]);

        $this->assertEquals(100, $user['points']);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/transaction/execute/9e239bb3-cfb4-4783-92e0-18ce187041bc', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/transaction/execute/9e239bb3-cfb4-4783-92e0-18ce187041bc', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/transaction/execute/9e239bb3-cfb4-4783-92e0-18ce187041bc', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
