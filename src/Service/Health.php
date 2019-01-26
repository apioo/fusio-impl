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

namespace Fusio\Impl\Service;

use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Parameters;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * Health
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Health
{
    /**
     * @var \Fusio\Impl\Service\Connection 
     */
    protected $connectionService;

    /**
     * @var \Fusio\Impl\Table\Connection 
     */
    protected $connectionTable;

    /**
     * @var \Fusio\Engine\Factory\Connection
     */
    protected $connectionFactory;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @param \Fusio\Impl\Service\Connection $connectionService
     * @param \Fusio\Impl\Table\Connection $connectionTable
     * @param \Fusio\Engine\Factory\Connection $connectionFactory
     * @param string $secretKey
     */
    public function __construct(Service\Connection $connectionService, Table\Connection $connectionTable, Factory\Connection $connectionFactory, string $secretKey)
    {
        $this->connectionService = $connectionService;
        $this->connectionTable   = $connectionTable;
        $this->connectionFactory = $connectionFactory;
        $this->secretKey         = $secretKey;
    }

    /**
     * @return \Fusio\Impl\Service\Health\CheckResult
     */
    public function check()
    {
        $checks = new Service\Health\CheckResult();

        $condition  = new Condition();
        $condition->equals('status', Table\Connection::STATUS_ACTIVE);

        $result = $this->connectionTable->getAll(0, 1024, null, null, $condition);
        foreach ($result as $row) {
            $factory    = $this->connectionFactory->factory($row['class']);
            $parameters = Service\Connection::decryptConfig($row['class'], $this->secretKey);
            $connection = $factory->getConnection(new Parameters($parameters));

            if ($factory instanceof PingableInterface) {
                try {
                    $factory->ping($connection);

                    $checks->add($row['name'], true);
                } catch (\Throwable $e) {
                    $checks->add($row['name'], false, $e->getMessage());
                }
            }
        }

        return $checks;
    }
}
