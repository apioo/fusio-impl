<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Connection;

use Doctrine\DBAL\DriverManager;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;

/**
 * Memory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Memory implements ConnectionInterface
{
    /**
     * @var array
     */
    private static $connections = [];

    public function getName()
    {
        return 'Memory';
    }

    /**
     * @param \Fusio\Engine\ParametersInterface $config
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection(ParametersInterface $config)
    {
        $key = $config->get('key') ?: 'default';

        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = DriverManager::getConnection(array(
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ));
        }

        return self::$connections[$key];
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('key', 'Key', 'text', 'Optional a unique key of this connection'));
    }
}
