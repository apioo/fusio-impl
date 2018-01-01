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

    const TYPE_CONFIG = 'config';
    const TYPE_CONNECTION = 'connection';
    const TYPE_SCHEMA = 'schema';
    const TYPE_ACTION = 'action';
    const TYPE_ROUTES = 'routes';
    const TYPE_CRONJOB = 'cronjob';
    const TYPE_RATE = 'rate';

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
    protected $types = [
        self::TYPE_CONNECTION,
        self::TYPE_SCHEMA,
        self::TYPE_ACTION,
        self::TYPE_ROUTES,
        self::TYPE_CRONJOB,
        self::TYPE_RATE,
    ];

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
        $method = 'transform' . ucfirst($type);
        if (in_array($type, $this->types) && method_exists($this, $method)) {
            return $this->$method($entity);
        } else {
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
                // parameters
                if (!empty($row->parameters)) {
                    $entity->config[$index]->methods->{$method}->parameters = $this->getReference('fusio_schema', $row->parameters, self::TYPE_ROUTES);
                }

                // request
                if (!empty($row->request)) {
                    $entity->config[$index]->methods->{$method}->request = $this->getReference('fusio_schema', $row->request, self::TYPE_ROUTES);
                }

                // responses
                $responses = [];
                if (!empty($row->response)) {
                    $responses[200] = $this->getReference('fusio_schema', $row->response, self::TYPE_ROUTES);

                    // remove deprecated response field
                    unset($entity->config[$index]->methods->{$method}->response);
                } elseif (!empty($row->responses) && $row->responses instanceof stdClass) {
                    foreach ($row->responses as $code => $response) {
                        $code = (int) $code;
                        if ($code >= 200) {
                            $responses[$code] = $this->getReference('fusio_schema', $response, self::TYPE_ROUTES);
                        }
                    }
                }

                if (!empty($responses)) {
                    $entity->config[$index]->methods->{$method}->responses = $responses;
                }

                // action
                if (!empty($row->action)) {
                    $entity->config[$index]->methods->{$method}->action = $this->getReference('fusio_action', $row->action, self::TYPE_ROUTES);
                }
            }
        }

        return $entity;
    }

    protected function transformCronjob(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);
        unset($entity->executeDate);
        unset($entity->exitCode);
        unset($entity->errors);

        if (!empty($entity->action)) {
            $entity->action = $this->getReference('fusio_action', $entity->action, self::TYPE_CRONJOB);
        }

        return $entity;
    }

    protected function transformRate(stdClass $entity)
    {
        unset($entity->id);
        unset($entity->status);

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
                    $config->{$data['name']} = $this->getReference('fusio_action', $entity->config->{$data['name']}, $type);
                } elseif ($element instanceof Form\Element\Connection) {
                    $config->{$data['name']} = $this->getReference('fusio_connection', $entity->config->{$data['name']}, $type);
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
