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

use Doctrine\DBAL;
use Doctrine\DBAL\Schema\AbstractAsset;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use PSX\Framework\Config\Config;

/**
 * System
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class System implements ConnectionInterface, PingableInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'System';
    }

    public function getConnection(ParametersInterface $config): DBAL\Connection
    {
        $params = $this->config->get('psx_connection');
        $config = new DBAL\Configuration();
        $config->setSchemaAssetsFilter(static function($assetName) {
            if ($assetName instanceof AbstractAsset) {
                $assetName = $assetName->getName();
            }
            return preg_match('~^(?!fusio_)~', $assetName);
        });

        return DBAL\DriverManager::getConnection($params, $config);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
    }

    public function ping(mixed $connection): bool
    {
        if ($connection instanceof DBAL\Connection) {
            return $connection->ping();
        }

        return false;
    }
}
