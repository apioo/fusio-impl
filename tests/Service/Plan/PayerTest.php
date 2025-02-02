<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Tests\Service\Plan;

use Fusio\Engine\Context;
use Fusio\Engine\Model\App;
use Fusio\Engine\Model\User;
use Fusio\Impl\Service\Config;
use Fusio\Impl\Service\Plan\Payer;
use Fusio\Impl\Service\User\Mailer;
use Fusio\Impl\Table;
use PHPUnit\Framework\TestCase;

/**
 * PayerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class PayerTest extends TestCase
{
    /**
     * @dataProvider pointsProvider
     */
    public function testPay(?int $threshold, int $points, int $cost, bool $sendMail)
    {
        $userTable = $this->getMockBuilder(Table\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userTable->expects($this->once())
            ->method('payPoints')
            ->with(1, $cost);

        $usageTable = $this->getMockBuilder(Table\Plan\Usage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $usageTable->expects($this->once())
            ->method('create')
            ->with($this->callback(function($row) use ($cost) {
                /** @var Table\Generated\PlanUsageRow $row */
                $this->assertInstanceOf(Table\Generated\PlanUsageRow::class, $row);
                $this->assertEquals(1, $row->getOperationId());
                $this->assertEquals(1, $row->getUserId());
                $this->assertEquals(1, $row->getAppId());
                $this->assertEquals($cost, $row->getPoints());
                return true;
            }));

        $configService = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configService->expects($this->once())
            ->method('getValue')
            ->withAnyParameters()
            ->willReturn($this->returnValue($threshold));

        $mailerService = $this->getMockBuilder(Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailerService->expects($sendMail ? $this->once() : $this->never())
            ->method('sendPointsThresholdMail')
            ->with('foo', 'foo@bar.com', $points);

        $app = new App(false, 1, 1, 1, '', '', '', [], []);
        $user = new User(false, 1, 1, 1, 1, 'foo', 'foo@bar.com', $points);
        $context = new Context(1, '/', $app, $user);

        $payer = new Payer($userTable, $usageTable, $configService, $mailerService);
        $payer->pay($cost, $context);
    }

    public static function pointsProvider(): array
    {
        return [
            [100, 104, 1, false],
            [100, 103, 1, false],
            [100, 102, 1, false],
            [100, 101, 1, false],
            [100, 100, 1, true],
            [100, 99, 1, false],
            [100, 98, 1, false],
            [100, 97, 1, false],
            [100, 96, 1, false],

            [100, 104, 2, false],
            [100, 103, 2, false],
            [100, 102, 2, false],
            [100, 101, 2, true],
            [100, 100, 2, true],
            [100, 99, 2, false],
            [100, 98, 2, false],
            [100, 97, 2, false],
            [100, 96, 2, false],

            [100, 104, 4, false],
            [100, 103, 4, true],
            [100, 102, 4, true],
            [100, 101, 4, true],
            [100, 100, 4, true],
            [100, 99, 4, false],
            [100, 98, 4, false],
            [100, 97, 4, false],
            [100, 96, 4, false],

            [0, 104, 1, false],
            [0, 103, 1, false],
            [0, 102, 1, false],
            [0, 101, 1, false],
            [0, 100, 1, false],
            [0, 99, 1, false],
            [0, 98, 1, false],
            [0, 97, 1, false],
            [0, 96, 1, false],

            [null, 104, 1, false],
            [null, 103, 1, false],
            [null, 102, 1, false],
            [null, 101, 1, false],
            [null, 100, 1, false],
            [null, 99, 1, false],
            [null, 98, 1, false],
            [null, 97, 1, false],
            [null, 96, 1, false],
        ];
    }
}
