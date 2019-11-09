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

namespace Fusio\Impl\Connection;

use Doctrine\DBAL;
use Doctrine\DBAL\Schema\AbstractAsset;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Factory\ContainerAwareInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Psr\Container\ContainerInterface;

/**
 * System
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class System implements ConnectionInterface, ContainerAwareInterface, PingableInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    public function getName()
    {
        return 'System';
    }

    /**
     * @param \Fusio\Engine\ParametersInterface $config
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection(ParametersInterface $config)
    {
        $params = $this->container->get('config')->get('psx_connection');
        $config = new DBAL\Configuration();
        $config->setSchemaAssetsFilter(static function($assetName) {
            if ($assetName instanceof AbstractAsset) {
                $assetName = $assetName->getName();
            }
            return preg_match('~^(?!fusio_)~', $assetName);
        });

        return DBAL\DriverManager::getConnection($params, $config);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function ping($connection)
    {
        if ($connection instanceof DBAL\Connection) {
            return $connection->ping();
        }

        return false;
    }
}
