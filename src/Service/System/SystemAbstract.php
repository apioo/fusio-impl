<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Form;
use Fusio\Engine\Parser\ParserInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;

/**
 * SystemAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class SystemAbstract
{
    const COLLECTION_SIZE = 16;

    const TYPE_CONNECTION = 'connection';
    const TYPE_SCHEMA = 'schema';
    const TYPE_ACTION = 'action';
    const TYPE_ROUTES = 'routes';

    /**
     * @var \Fusio\Impl\Service\System\ApiExecutor
     */
    protected $apiExecutor;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $actionParser;

    /**
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $connectionParser;

    /**
     * @var array
     */
    protected $types = [self::TYPE_CONNECTION, self::TYPE_SCHEMA, self::TYPE_ACTION, self::TYPE_ROUTES];

    /**
     * @param \Fusio\Impl\Service\System\ApiExecutor $apiExecutor
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Fusio\Engine\Parser\ParserInterface $actionParser
     * @param \Fusio\Engine\Parser\ParserInterface $connectionParser
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(ApiExecutor $apiExecutor, Connection $connection, ParserInterface $actionParser, ParserInterface $connectionParser, LoggerInterface $logger)
    {
        $this->apiExecutor      = $apiExecutor;
        $this->connection       = $connection;
        $this->actionParser     = $actionParser;
        $this->connectionParser = $connectionParser;
        $this->logger           = $logger;
    }

    protected function doRequest($method, $endpoint, $body = null)
    {
        return $this->apiExecutor->request($method, $endpoint, $body);
    }

    protected function transform($type, stdClass $entity)
    {
        switch ($type) {
            case self::TYPE_CONNECTION:
                return $this->transformConnection($entity);
                break;

            case self::TYPE_SCHEMA:
                return $this->transformSchema($entity);
                break;

            case self::TYPE_ACTION:
                return $this->transformAction($entity);
                break;

            case self::TYPE_ROUTES:
                return $this->transformRoutes($entity);
                break;

            default:
                throw new RuntimeException('Invalid type');
        }
    }

    protected function transformConnection(stdClass $entity)
    {
        unset($entity->id);

        $form   = $this->connectionParser->getForm($entity->class);
        $entity = $this->handleFormReferences($entity, $form, self::TYPE_CONNECTION);

        return $entity;
    }

    protected function transformSchema(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

        return $entity;
    }

    protected function transformAction(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

        $form   = $this->actionParser->getForm($entity->class);
        $entity = $this->handleFormReferences($entity, $form, self::TYPE_ACTION);

        return $entity;
    }

    protected function transformRoutes(stdClass $entity)
    {
        unset($entity->id);

        $config = isset($entity->config) ? $entity->config : [];

        if (!is_array($config)) {
            throw new RuntimeException('Config must be an array');
        }

        foreach ($config as $index => $version) {
            $methods = isset($version->methods) ? $version->methods : [];

            foreach ($methods as $method => $row) {
                if (!empty($row->action)) {
                    $name = $this->getReference('fusio_action', $row->action, self::TYPE_ROUTES);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve action ' . $row->action);
                    }
                    $entity->config[$index]->methods->{$method}->action = $name;
                }

                if (!empty($row->request)) {
                    $name = $this->getReference('fusio_schema', $row->request, self::TYPE_ROUTES);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve schema ' . $row->request);
                    }
                    $entity->config[$index]->methods->{$method}->request = $name;
                }

                if (!empty($row->response)) {
                    $name = $this->getReference('fusio_schema', $row->response, self::TYPE_ROUTES);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve schema ' . $row->response);
                    }
                    $entity->config[$index]->methods->{$method}->response = $name;
                }
            }
        }

        return $entity;
    }

    protected function handleFormReferences(stdClass $entity, $form, $type)
    {
        $config = new stdClass();

        if ($form instanceof Form\Container) {
            $elements = $form->getElements();
            foreach ($elements as $element) {
                $data = $element->getProperties();

                if (!isset($entity->config->{$data['name']})) {
                    continue;
                }

                if ($element instanceof Form\Element\Action) {
                    $name = $this->getReference('fusio_action', $entity->config->{$data['name']}, $type);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve action ' . $entity->config->{$data['name']});
                    }
                    $config->{$data['name']} = $name;
                } elseif ($element instanceof Form\Element\Connection) {
                    $name = $this->getReference('fusio_connection', $entity->config->{$data['name']}, $type);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve connection ' . $entity->config->{$data['name']});
                    }
                    $config->{$data['name']} = $name;
                } else {
                    $config->{$data['name']} = $entity->config->{$data['name']};
                }
            }
        }

        $entity->config = $config;

        return $entity;
    }

    /**
     * Returns a reference either an id or name
     *
     * @param string $table
     * @param string $id
     * @param string $type
     * @return mixed
     */
    abstract protected function getReference($table, $id, $type);
}
