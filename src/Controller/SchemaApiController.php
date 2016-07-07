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

use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Authorization\Oauth2Filter;
use Fusio\Impl\Context as FusioContext;
use Fusio\Impl\Processor\RepositoryInterface;
use Fusio\Impl\Request;
use Fusio\Impl\Schema\LazySchema;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Api\Resource\MethodAbstract;
use PSX\Data\Record\Transformer;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Filter\CORS;
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
     * @var \Fusio\Impl\Processor
     */
    protected $processor;

    /**
     * @Inject
     * @var \Fusio\Impl\Data\SchemaManager
     */
    protected $apiSchemaManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Logger
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
     * @var \Fusio\Impl\Service\Routes\Method
     */
    protected $routesMethodService;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

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

        // cors header
        $allowOrigin = $this->configService->getValue('cors_allow_origin');
        if (!empty($allowOrigin)) {
            $filter[] = new CORS($allowOrigin);
        }

        if (!$isPublic) {
            $filter[] = new Oauth2Filter($this->connection, $this->request->getMethod(), $this->context->get('fusio.routeId'), function ($accessToken) {

                $this->appId  = $accessToken['appId'];
                $this->userId = $accessToken['userId'];

            });
        }

        return $filter;
    }

    /**
     * Select all methods from the routes method table and build a resource 
     * based on the data. If the route is in production mode read the schema 
     * from the cache else resolve it
     * 
     * @param integer $version
     * @return null|Resource
     */
    public function getDocumentation($version = null)
    {
        return $this->routesMethodService->getDocumentation(
            $this->context->get('fusio.routeId'),
            $version,
            $this->context->get(Context::KEY_PATH)
        );
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
                return Transformer::toRecord($this->getBody());
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
        $method   = $this->getActiveMethod();
        $context  = new FusioContext($this->context->get('fusio.routeId'), $this->app, $this->user);
        $request  = new Request($this->request, $this->uriFragments, $this->getParameters(), $record);
        $response = null;
        $actionId = $method['action'];

        if ($actionId > 0) {
            if ($method['status'] != Resource::STATUS_DEVELOPMENT) {
                // if the method is not in dev mode we load the action from the
                // cache
                $repository = unserialize($method['actionCache']);

                if ($repository instanceof RepositoryInterface) {
                    $this->processor->push($repository);

                    try {
                        $response = $this->processor->execute($actionId, $request, $context);
                    } catch (\Exception $e) {
                        $this->apiLogger->appendError($this->logId, $e);

                        throw $e;
                    }

                    $this->processor->pop();
                } else {
                    throw new StatusCode\ServiceUnavailableException('Invalid action cache');
                }
            } else {
                // if the action is in dev mode we load the values direct from
                // the table
                try {
                    $response = $this->processor->execute($actionId, $request, $context);
                } catch (\Exception $e) {
                    $this->apiLogger->appendError($this->logId, $e);

                    throw $e;
                }
            }
        } else {
            throw new StatusCode\ServiceUnavailableException('No action provided');
        }

        if ($response instanceof ResponseInterface) {
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
        } else {
            throw new StatusCode\InternalServerErrorException('Invalid action response');
        }
    }

    private function getActiveMethod()
    {
        if ($this->activeMethod) {
            return $this->activeMethod;
        }

        $version = $this->getSubmittedVersionNumber();
        $method  = $this->routesMethodService->getMethod(
            $this->context->get('fusio.routeId'),
            $version,
            $this->request->getMethod()
        );

        if (empty($method)) {
            $allowedMethods = $this->routesMethodService->getAllowedMethods(
                $this->context->get('fusio.routeId'),
                $version
            );

            throw new StatusCode\MethodNotAllowedException('Given request method is not supported', $allowedMethods);
        }

        return $this->activeMethod = $method;
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

        return isset($matches[2]) ? $matches[2] : null;
    }
}
