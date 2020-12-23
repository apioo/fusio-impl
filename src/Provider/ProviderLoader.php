<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Provider;

use Doctrine\DBAL\Connection;
use Fusio\Adapter;

/**
 * ProviderLoader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderLoader
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $file;

    /**
     * @var \Fusio\Impl\Provider\ProviderConfig
     */
    private $config;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param string $file
     */
    public function __construct(Connection $connection, $file)
    {
        $this->connection = $connection;
        $this->file       = $file;
    }

    /**
     * Returns the provider config of the system
     * 
     * @return \Fusio\Impl\Provider\ProviderConfig
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }

        $config = $this->getDefaultConfig();

        // read from database
        $result = $this->connection->fetchAll('SELECT type, class FROM fusio_provider ORDER BY class ASC');
        foreach ($result as $row) {
            $this->mergeClass($config, $row['type'], $row['class']);
        }

        // read from file
        if (!empty($this->file)) {
            $result = include($this->file);

            if (is_array($result)) {
                foreach ($result as $type => $classes) {
                    foreach ($classes as $class) {
                        $this->mergeClass($config, $type, $class);
                    }
                }
            }
        }

        return $this->config = new ProviderConfig($config);
    }

    public function reset()
    {
        $this->config = null;
    }

    private function mergeClass(array &$config, $type, $class)
    {
        if (isset($config[$type])) {
            if (class_exists($class) && !in_array($class, $config[$type])) {
                $config[$type][] = $class;
            }
        }
    }

    private function getDefaultConfig()
    {
        return [
            ProviderConfig::TYPE_ACTION => [
                Adapter\File\Action\FileProcessor::class,
                Adapter\GraphQL\Action\GraphQLProcessor::class,
                Adapter\Http\Action\HttpProcessor::class,
                Adapter\Php\Action\PhpProcessor::class,
                Adapter\Php\Action\PhpSandbox::class,
                Adapter\Smtp\Action\SmtpSend::class,
                Adapter\Sql\Action\SqlSelectAll::class,
                Adapter\Sql\Action\SqlSelectRow::class,
                Adapter\Sql\Action\SqlInsert::class,
                Adapter\Sql\Action\SqlUpdate::class,
                Adapter\Sql\Action\SqlDelete::class,
                Adapter\Sql\Action\Query\SqlQueryAll::class,
                Adapter\Sql\Action\Query\SqlQueryRow::class,
                Adapter\Util\Action\UtilStaticResponse::class,
            ],
            ProviderConfig::TYPE_CONNECTION => [
                Adapter\Http\Connection\Http::class,
                Adapter\Sql\Connection\Sql::class,
                Adapter\Sql\Connection\SqlAdvanced::class,
            ],
            ProviderConfig::TYPE_PAYMENT => [
            ],
            ProviderConfig::TYPE_USER => [
                User\Facebook::class,
                User\Github::class,
                User\Google::class,
            ],
            ProviderConfig::TYPE_ROUTES => [
                Adapter\Sql\Routes\SqlTable::class,
            ],
            ProviderConfig::TYPE_PUSH => [
            ],
        ];
    }
}
