<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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
use Fusio\Impl\Service\Plan\Creditor;
use Fusio\Impl\Table;
use PHPUnit\Framework\TestCase;

/**
 * CreditorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CreditorTest extends TestCase
{
    /**
     * @dataProvider pointsProvider
     */
    public function testCredit(int $points, int $cost)
    {
        $userTable = $this->getMockBuilder(Table\User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userTable->expects($this->once())
            ->method('creditPoints')
            ->with(1, $cost);

        $app = new App(false, 1, 1, 1, '', '', '', [], []);
        $user = new User(false, 1, 1, 1, 1, 'foo', 'foo@bar.com', $points);
        $context = new Context(1, '/', $app, $user);

        $creditor = new Creditor($userTable);
        $creditor->credit($cost, $context);
    }

    public static function pointsProvider(): array
    {
        return [
            [104, 1],
            [103, 1],
            [102, 1],
            [101, 1],
            [100, 1],
            [99, 1],
            [98, 1],
            [97, 1],
            [96, 1],

            [104, 2],
            [103, 2],
            [102, 2],
            [101, 2],
            [100, 2],
            [99, 2],
            [98, 2],
            [97, 2],
            [96, 2],
        ];
    }
}
