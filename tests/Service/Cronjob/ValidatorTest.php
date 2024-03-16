<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Service\Cronjob;

use Fusio\Impl\Backend\Filter\Cronjob\Cron;
use Fusio\Impl\Service\Cronjob\Validator;
use Fusio\Model\Backend\CronjobCreate;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\BadRequestException;

/**
 * ValidatorTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ValidatorTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testAssertCron(string $cron, bool $expect, ?string $errorMessage)
    {
        try {
            $cronjob = new CronjobCreate();
            $cronjob->setName('test');
            $cronjob->setCron($cron);
            Environment::getService(Validator::class)->assert($cronjob, null);

            $this->assertTrue($expect);
        } catch (BadRequestException $e) {
            $this->assertFalse($expect);
            $this->assertEquals($errorMessage, $e->getMessage());
        }
    }

    public function pathProvider()
    {
        return [
            ['', false, 'Cron must not be empty'],
            ['foo', false, 'foo is not a valid CRON expression'],
            ['59 0 a * *', false, 'Invalid CRON field value a at position 2'],
            ['60 0 * * *', false, 'Invalid CRON field value 60 at position 0'],
            ['5 24 * * *', false, 'Invalid CRON field value 24 at position 1'],
            ['5 0 32 * *', false, 'Invalid CRON field value 32 at position 2'],
            ['5 0 0 * *', false, 'Invalid CRON field value 0 at position 2'],
            ['5 0 * 13 *', false, 'Invalid CRON field value 13 at position 3'],
            ['5 0 * 0 *', false, 'Invalid CRON field value 0 at position 3'],
            ['5 0 * * 8', false, 'Invalid CRON field value 8 at position 4'],

            ['5 0 * * *', true, null],
            ['*/5 * * * *', true, null],
            ['15 14 1 * *', true, null],
            ['0 22 * * 1-5', true, null],
            ['23 0-23/2 * * *', true, null],
            ['5 4 * * sun', true, null], // we convert names
            ['0 4 8-14 * *', true, null],
            ['@daily', true, null],

            ['5  0  *    *  *', true, null], // fix spaces
            ['05 00 * * *', true, null], // convert to int
        ];
    }
}
