<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Base;
use Fusio\Impl\Form;
use Monolog\Handler\NullHandler;
use Psr\Log\LoggerInterface;
use DateTime;
use PSX\Framework\Dispatch\Dispatch;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\TempStream;
use PSX\Json\Parser;
use PSX\Uri\Url;
use RuntimeException;
use stdClass;

/**
 * SystemAbstract
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class SystemAbstract
{
    const COLLECTION_SIZE = 16;

    protected $dispatch;
    protected $connection;
    protected $actionParser;
    protected $connectionParser;
    protected $logger;

    protected $types = ['connection', 'database', 'schema', 'action', 'routes'];
    protected $accessToken;

    public function __construct(Dispatch $dispatch, Connection $connection, ParserInterface $actionParser, ParserInterface $connectionParser, LoggerInterface $logger)
    {
        $this->dispatch         = $dispatch;
        $this->connection       = $connection;
        $this->actionParser     = $actionParser;
        $this->connectionParser = $connectionParser;
        $this->logger           = $logger;
    }

    protected function doRequest($method, $endpoint, $body = null)
    {
        $header   = ['User-Agent' => 'Fusio-System v' . Base::getVersion(), 'Authorization' => 'Bearer ' . $this->getAccessToken()];
        $body     = $body !== null ? Parser::encode($body) : null;
        $request  = new Request(new Url('http://127.0.0.1/backend/' . $endpoint), $method, $header, $body);
        $response = new Response();
        $response->setBody(new TempStream(fopen('php://memory', 'r+')));

        $this->logger->pushHandler(new NullHandler());

        $this->dispatch->route($request, $response, null, false);

        $this->logger->popHandler();

        $body = (string) $response->getBody();
        $data = Parser::decode($body, false);

        return $data;
    }

    protected function getAccessToken()
    {
        if (empty($this->accessToken)) {
            // insert access token
            $token  = TokenGenerator::generateToken();
            $expire = new DateTime('+30 minute');
            $now    = new DateTime();

            $this->connection->insert('fusio_app_token', [
                'appId'  => 1,
                'userId' => 1,
                'status' => 1,
                'token'  => $token,
                'scope'  => 'backend',
                'ip'     => '127.0.0.1',
                'expire' => $expire->format('Y-m-d H:i:s'),
                'date'   => $now->format('Y-m-d H:i:s'),
            ]);

            return $this->accessToken = $token;
        } else {
            return $this->accessToken;
        }
    }

    protected function transform($type, stdClass $entity)
    {
        switch ($type) {
            case 'connection':
                return $this->transformConnection($entity);
                break;

            case 'database':
                return $this->transformDatabase($entity);
                break;

            case 'schema':
                return $this->transformSchema($entity);
                break;

            case 'action':
                return $this->transformAction($entity);
                break;

            case 'routes':
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
        $entity = $this->handleFormReferences($entity, $form);

        return $entity;
    }

    protected function transformDatabase(stdClass $entity)
    {
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
        $entity = $this->handleFormReferences($entity, $form);

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
                if (isset($row->action)) {
                    $name = $this->getReference('fusio_action', $row->action);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve action ' . $row->action);
                    }
                    $entity->config[$index]->methods->{$method}->action = $name;
                }

                if (isset($row->request)) {
                    $name = $this->getReference('fusio_schema', $row->request);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve schema ' . $row->request);
                    }
                    $entity->config[$index]->methods->{$method}->request = $name;
                }

                if (isset($row->response)) {
                    $name = $this->getReference('fusio_schema', $row->response);
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve schema ' . $row->response);
                    }
                    $entity->config[$index]->methods->{$method}->response = $name;
                }
            }
        }

        return $entity;
    }

    protected function handleFormReferences(stdClass $entity, $form)
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
                    $name = $this->getReference('fusio_action', $entity->config->{$data['name']});
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve action ' . $entity->config->{$data['name']});
                    }
                    $config->{$data['name']} = $name;
                } elseif ($element instanceof Form\Element\Connection) {
                    $name = $this->getReference('fusio_connection', $entity->config->{$data['name']});
                    if (empty($name)) {
                        throw new RuntimeException('Could not resolve connection ' . $entity->config->{$data['name']});
                    }
                    $config->{$data['name']} = $name;
                } elseif ($element instanceof Form\Element\Input) {
                    if ($data['type'] == 'password') {
                        // dont export or import password fields
                        $config->{$data['name']} = "";
                    } else {
                        $config->{$data['name']} = $entity->config->{$data['name']};
                    }
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
     * @return mixed
     */
    abstract protected function getReference($table, $id);
}
