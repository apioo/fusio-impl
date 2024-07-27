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

namespace Fusio\Impl\Tests;

use Fusio\Engine\Connector;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * DbTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DbTestCase extends ControllerDbTestCase
{
    private static bool $initialized = false;
    private static array $tableNames = [];

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    protected function setUp(): void
    {
        if (!self::$initialized) {
            parent::setup();

            self::$tableNames = $this->connection->createSchemaManager()->listTableNames();
            self::$initialized = true;
        } else {
            $this->connection = $this->getConnection();
        }

        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        while ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }

        $this->clearState();
    }

    private function clearState(): void
    {
        $connector = Environment::getService('test_connector');
        if ($connector instanceof Connector) {
            $connector->clear();
        }
    }
}
