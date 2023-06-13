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

namespace Fusio\Impl\Tests\Migrations;

use Fusio\Impl\Backend;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\DbTestCase;
use PSX\Sql\Generator\Generator;

/**
 * GenerateTableTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GenerateTableTest extends DbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGenerate()
    {
        $this->markTestSkipped();

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