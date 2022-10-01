<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Migrations;

use Fusio\Impl\Backend;
use Fusio\Impl\Migrations\DataSyncronizer;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Table\Config;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\DbTestCase;
use PSX\Sql\Generator\Generator;

/**
 * GenerateTableTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GenerateTableTest extends DbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGenerate()
    {
        #$this->markTestSkipped();

        $target = __DIR__ . '/../../src/Table/Generated';
        $namespace = 'Fusio\Impl\Table\Generated';

        $generator = new Generator($this->connection, $namespace, 'fusio_');
        $count = 0;
        foreach ($generator->generate() as $className => $source) {
            file_put_contents($target . '/' . $className . '.php', '<?php' . "\n\n" . $source);
            $count++;
        }

        $this->assertNotEmpty($count);
    }
}