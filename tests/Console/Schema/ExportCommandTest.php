<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Console\Schema;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExportCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExportCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('schema:export');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'name'    => 'Foo-Schema',
        ]);

        $actual = $commandTester->getDisplay();
        $expect = <<<'JSON'
{
    "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
    "id": "urn:schema.phpsx.org#",
    "definitions": {
        "ref11328c18443d238942720184135b4d00": {
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
            },
            "additionalProperties": false
        }
    },
    "title": "test",
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
                "$ref": "#\/definitions\/ref11328c18443d238942720184135b4d00"
            }
        }
    },
    "additionalProperties": false
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}
