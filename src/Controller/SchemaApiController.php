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

namespace Fusio\Impl\Controller;

use Fusio\Impl\Authorization\Oauth2Filter;
use Fusio\Impl\Context as FusioContext;
use Fusio\Impl\Request;
use Fusio\Impl\Schema\LazySchema;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Api\Resource\MethodAbstract;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Record\Record;
use PSX\Record\RecordInterface;
use PSX\Schema\SchemaInterface;
use PSX\Framework\Filter\UserAgentEnforcer;
use PSX\Http\Exception as StatusCode;
use PSX\Framework\Loader\Context;

/**
 * SchemaApiController
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SchemaApiController extends SchemaApiAbstract implements DocumentedInterface
{
    const SCHEMA_PASSTHRU = 'passthru';

    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @Inject
     * @var \Fusio\Engine\ProcessorInterface
     */
    protected $processor;

    /**
     * @Inject
     * @var \Fusio\Impl\Data\SchemaManager
     */
    protected $apiSchemaManager;

    /**
     * @Inject
     * @var \Fusio\Engine\LoggerInterface
     */
    protected $apiLogger;

    /**
     * @Inject
     * @var \Fusio\Engine\Schema\LoaderInterface
     */
    protected $schemaLoader;

    /**
     * @Inject
     * @var \Fusio\Engine\App\LoaderInterface
     */
    protected $appLoader;

    /**
     * @Inject
     * @var \Fusio\Engine\User\LoaderInterface
     */
    protected $userLoader;

    /**
     * @Inject
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var \Fusio\Engine\Model\AppInterface
     */
    protected $app;

    /**
     * @var \Fusio\Engine\Model\UserInterface
     */
    protected $user;

    /**
     * @var integer
     */
    protected $appId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var integer
     */
    protected $logId;

    private $activeMethod;

    public function onLoad()
    {
        parent::onLoad();

        // load app
        $this->app  = $this->appLoader->getById($this->appId);
        $this->user = $this->userLoader->getById($this->userId);

        // log request
        $this->logId = $this->apiLogger->log(
            $this->context->get('fusio.routeId'),
            $this->appId,
            $this->userId,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            $this->request
        );
    }

    public function getPreFilter()
    {
        $isPublic = $this->getActiveMethod()->public;
        $filter   = array();

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        if (!$isPublic) {
            $filter[] = new Oauth2Filter($this->connection, $this->request->getMethod(), $this->context->get('fusio.routeId'), function ($accessToken) {

                $this->appId  = $accessToken['appId'];
                $this->userId = $accessToken['userId'];

            });
        }

        return $filter;
    }

    public function getDocumentation($version = null)
    {
        $versions = $this->context->get('fusio.config');
        $config   = null;

        if ($version == '*' || empty($version)) {
            $version = $this->getLatestVersionFromConfig();
        }

        foreach ($versions as $row) {
            if ($row->name == $version) {
                $config = $row;
                break;
            }
        }

        if ($config instanceof RecordInterface) {
            $resource = new Resource($config->status, $this->context->get(Context::KEY_PATH));
            $methods  = $config->methods;

            foreach ($methods as $method) {
                if ($method->active) {
                    $resourceMethod = Resource\Factory::getMethod($method->name);

                    if (is_int($method->request)) {
                        $resourceMethod->setRequest(new LazySchema($this->schemaLoader, $method->request));
                    } elseif ($method->request instanceof SchemaInterface) {
                        $resourceMethod->setRequest($method->request);
                    }

                    if (is_int($method->response)) {
                        $resourceMethod->addResponse(200, new LazySchema($this->schemaLoader, $method->response));
                    } elseif ($method->response instanceof SchemaInterface) {
                        $resourceMethod->addResponse(200, $method->response);
                    }

                    $resource->addMethod($resourceMethod);
                }
            }

            return $resource;
        }

        return null;
    }

    protected function doGet()
    {
        return $this->executeAction(new Record());
    }

    protected function doPost($record)
    {
        return $this->executeAction($record);
    }

    protected function doPut($record)
    {
        return $this->executeAction($record);
    }

    protected function doDelete($record)
    {
        return $this->executeAction($record);
    }

    protected function parseRequest(MethodAbstract $method)
    {
        if ($method->hasRequest()) {
            if ($method->getRequest()->getDefinition()->getName() == self::SCHEMA_PASSTHRU) {
                return $this->getBody();
            } else {
                return $this->getBodyAs($method->getRequest());
            }
        } else {
            return new Record();
        }
    }

    protected function sendResponse(MethodAbstract $method, $response)
    {
        $statusCode = $this->response->getStatusCode();
        if (!empty($statusCode) && $method->hasResponse($statusCode)) {
            $schema = $method->getResponse($statusCode);
        } else {
            $schema = $this->getSuccessfulResponse($method, $statusCode);
        }

        if ($schema instanceof SchemaInterface) {
            $this->setResponseCode($statusCode);

            if ($schema->getDefinition()->getName() == self::SCHEMA_PASSTHRU) {
                $this->setBody($response);
            } else {
                $this->setBodyAs($schema, $response);
            }
        } else {
            $this->setResponseCode(204);
            $this->setBody('');
        }
    }

    private function executeAction($record)
    {
        $actionId = $this->getActiveMethod()->action;

        if (is_int($actionId)) {
            try {
                $context    = new FusioContext($this->context->get('fusio.routeId'), $this->app, $this->user);
                $request    = new Request($this->request, $this->uriFragments, $this->getParameters(), $record);
                $response   = $this->processor->execute($actionId, $request, $context);
                $statusCode = $response->getStatusCode();
                $headers    = $response->getHeaders();

                if (!empty($statusCode)) {
                    $this->setResponseCode($statusCode);
                }

                if (!empty($headers)) {
                    foreach ($headers as $name => $value) {
                        $this->setHeader($name, $value);
                    }
                }

                return $response->getBody();
            } catch (\Exception $e) {
                $this->apiLogger->appendError($this->logId, $e);

                throw $e;
            }
        } else {
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }
    }

    private function getActiveMethod()
    {
        if ($this->activeMethod) {
            return $this->activeMethod;
        }

        $version = $this->getSubmittedVersionNumber();
        $method  = $this->getAvailableMethod($version);

        if ($method === null) {
            throw new StatusCode\MethodNotAllowedException('Given request method is not supported', $this->getAllowedMethods($version));
        }

        return $this->activeMethod = $method;
    }

    private function getAvailableMethod($version)
    {
        $config = $this->context->get('fusio.config');

        foreach ($config as $resource) {
            if ($resource->name == $version) {
                $methods = $resource->methods;
                foreach ($methods as $method) {
                    if ($method->name == $this->request->getMethod() && $method->active) {
                        return $method;
                    }
                }
            }
        }

        return null;
    }

    private function getAllowedMethods($version)
    {
        $config  = $this->context->get('fusio.config');
        $allowed = [];

        foreach ($config as $resource) {
            if ($resource->name == $version) {
                $methods = $resource->methods;
                foreach ($methods as $method) {
                    if ($method->active) {
                        $allowed[] = $method->name;
                    }
                }
            }
        }

        return $allowed;
    }

    /**
     * Returns the version number which was submitted by the client in the
     * accept header field
     *
     * @return integer
     */
    private function getSubmittedVersionNumber()
    {
        $accept  = $this->getHeader('Accept');
        $matches = array();

        preg_match('/^application\/vnd\.([a-z.-_]+)\.v([\d]+)\+([a-z]+)$/', $accept, $matches);

        $version = isset($matches[2]) ? $matches[2] : null;

        // if null get latest version
        if ($version === null) {
            $version = $this->getLatestVersionFromConfig();
        }

        return $version;
    }
    
    private function getLatestVersionFromConfig()
    {
        $config   = $this->context->get('fusio.config');
        $versions = [];

        foreach ($config as $resource) {
            $versions[] = (int) $resource->name;
        }
        rsort($versions);

        return reset($versions);
    }
}
