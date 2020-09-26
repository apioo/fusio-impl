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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Parser\ParserInterface;
use Fusio\Impl\Provider\ProviderWriter;
use Fusio\Impl\Service\System\Import\Result;
use Psr\Log\LoggerInterface;
use PSX\Json\Parser;
use RuntimeException;
use stdClass;

/**
 * Import
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Import extends SystemAbstract
{
    /**
     * @var \Fusio\Impl\Provider\ProviderWriter
     */
    protected $providerWriter;

    /**
     * @param \Fusio\Impl\Service\System\ApiExecutor $apiExecutor
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Fusio\Engine\Parser\ParserInterface $actionParser
     * @param \Fusio\Engine\Parser\ParserInterface $connectionParser
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Fusio\Impl\Provider\ProviderWriter $providerWriter
     */
    public function __construct(ApiExecutor $apiExecutor, Connection $connection, ParserInterface $actionParser, ParserInterface $connectionParser, LoggerInterface $logger, ProviderWriter $providerWriter)
    {
        parent::__construct($apiExecutor, $connection, $actionParser, $connectionParser, $logger);

        $this->providerWriter = $providerWriter;
    }

    /**
     * @param string $data
     * @return \Fusio\Impl\Service\System\Import\Result
     */
    public function import($data)
    {
        $data   = Parser::decode($data, false);
        $result = new Result();

        if (!$data instanceof stdClass) {
            throw new RuntimeException('Data must be an object');
        }

        // check whether the adapter wants to add a new provider classes to the
        // provider.php file
        $this->importProvider($data, $result);

        $config = isset($data->config) ? $data->config : null;
        if (!empty($config) && $config instanceof stdClass) {
            $this->importConfig($config, $result);
        }

        foreach ($this->types as $type) {
            $entries = isset($data->{$type}) ? $data->{$type} : null;
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (!$entry instanceof stdClass) {
                        continue;
                    }

                    if ($type === self::TYPE_ROUTE) {
                        $this->importRoutes($type, $entry, $result);
                    } else {
                        $this->importGeneral($type, $entry, $result);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @param \stdClass $data
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    private function importGeneral(string $type, stdClass $data, Result $result)
    {
        $name = $data->name;
        $id   = $this->connection->fetchColumn('SELECT id FROM fusio_' . $type . ' WHERE name = :name', [
            'name' => $name
        ]);

        if (!empty($id)) {
            $response = $this->doRequest('PUT', $type . '/' . $id, $data);
        } else {
            $response = $this->doRequest('POST', $type, $data);
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            $result->add($type, Result::ACTION_FAILED, $name . ': ' . $response->message);
        } elseif (!empty($id)) {
            $result->add($type, Result::ACTION_UPDATED, $name);
        } else {
            $result->add($type, Result::ACTION_CREATED, $name);
        }
    }

    /**
     * @param string $type
     * @param \stdClass $data
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    private function importRoutes(string $type, stdClass $data, Result $result)
    {
        $path = $data->path;
        $id   = $this->connection->fetchColumn('SELECT id FROM fusio_routes WHERE path = :path', [
            'path' => $path
        ]);

        if (!empty($id)) {
            $response = $this->doRequest('PUT', 'routes/' . $id, $data);
        } else {
            $response = $this->doRequest('POST', 'routes', $data);
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            $result->add(self::TYPE_ROUTE, Result::ACTION_FAILED, $path . ': ' . $response->message);
        } elseif (!empty($id)) {
            $result->add(self::TYPE_ROUTE, Result::ACTION_UPDATED, $path);
        } else {
            $result->add(self::TYPE_ROUTE, Result::ACTION_CREATED, $path);
        }
    }

    /**
     * @param \stdClass $config
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    private function importConfig(stdClass $config, Result $result)
    {
        $count = 0;
        foreach ($config as $name => $value) {
            if (!is_scalar($value)) {
                throw new RuntimeException('Config value must be scalar');
            }

            $id = $this->connection->fetchColumn('SELECT id FROM fusio_config WHERE name = :name', [
                'name' => $name,
            ]);

            if (!empty($id)) {
                $count+= $this->connection->update('fusio_config', [
                    'value' => $value,
                ], [
                    'id' => $id
                ]);
            } else {
                throw new RuntimeException('Unknown config parameter ' . $name);
            }
        }

        if ($count > 0) {
            $result->add(self::TYPE_CONFIG, Result::ACTION_UPDATED, 'Changed ' . $count . ' values');
        }
    }

    /**
     * @param \stdClass $data
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    private function importProvider(stdClass $data, Result $result)
    {
        $providerTypes  = $this->providerWriter->getAvailableTypes();
        $providerConfig = [];
        $newClasses     = [];

        foreach ($providerTypes as $providerType) {
            $name    = $providerType . 'Class';
            $classes = isset($data->{$name}) ? $data->{$name} : null;
            if (!empty($classes) && is_array($classes)) {
                $classes    = array_filter($classes, 'class_exists');
                $newClasses = array_merge($newClasses, $classes);

                $providerConfig[$providerType] = $classes;
            }
        }

        $count = $this->providerWriter->write($providerConfig);
        if ($count > 0) {
            foreach ($newClasses as $newClass) {
                $result->add('class', Result::ACTION_REGISTERED, $newClass);
            }
        }
    }
}
