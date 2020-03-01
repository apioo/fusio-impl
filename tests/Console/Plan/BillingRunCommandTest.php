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

namespace Fusio\Impl\Tests\Console\Plan;

use Fusio\Impl\Table\Plan\Invoice;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * BillingRunCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class BillingRunCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('plan:billing_run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertContains('Execution successful', $actual);

        $result = $this->connection->fetchAll('SELECT * FROM fusio_plan_invoice ORDER BY id ASC');

        $this->assertEquals(3, count($result));
        $this->assertEquals(1, $result[0]['contract_id']);
        $this->assertEquals(null, $result[0]['prev_id']);
        $this->assertEquals(Invoice::STATUS_PAYED, $result[0]['status']);
        $this->assertEquals(19.99, $result[0]['amount']);
        $this->assertEquals(100, $result[0]['points']);
        $this->assertEquals('2019-04-27', $result[0]['from_date']);
        $this->assertEquals('2019-04-27', $result[0]['to_date']);
        $this->assertEquals('2019-04-27 20:57:00', $result[0]['pay_date']);

        $this->assertEquals(1, $result[1]['contract_id']);
        $this->assertEquals(1, $result[1]['prev_id']);
        $this->assertEquals(Invoice::STATUS_OPEN, $result[1]['status']);
        $this->assertEquals(19.99, $result[1]['amount']);
        $this->assertEquals(100, $result[1]['points']);
        $this->assertEquals('2019-04-27', $result[1]['from_date']);
        $this->assertEquals('2019-04-27', $result[1]['to_date']);
        $this->assertEquals(null, $result[1]['pay_date']);

        $this->assertEquals(1, $result[2]['contract_id']);
        $this->assertEquals(2, $result[2]['prev_id']);
        $this->assertEquals(Invoice::STATUS_OPEN, $result[2]['status']);
        $this->assertEquals(19.99, $result[2]['amount']);
        $this->assertEquals(50, $result[2]['points']);
        $this->assertEquals('2019-04-27', $result[2]['from_date']);
        $this->assertEquals('2019-05-27', $result[2]['to_date']);
        $this->assertEquals(null, $result[2]['pay_date']);
    }
}
