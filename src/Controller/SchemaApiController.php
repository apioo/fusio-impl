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

namespace Fusio\Impl\Controller;

use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Authorization\Oauth2Filter;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Api\Resource\MethodAbstract;
use PSX\Data\Record\Transformer;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Filter\CORS;
use PSX\Framework\Filter\UserAgentEnforcer;
use PSX\Framework\Loader\Context;
use PSX\Http\Exception as StatusCode;
use PSX\Record\Record;

/**
 * SchemaApiController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
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
     * @var \Fusio\Engine\Processor
     */
    protected $processor;

    /**
     * @Inject
     * @var \Fusio\Impl\Logger
     */
    protected $apiLogger;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\AppInterface
     */
    protected $appRepository;

    /**
     * @Inject
     * @var \Fusio\Engine\Repository\UserInterface
     */
    protected $userRepository;

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
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

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

        // get request ip
        $remoteIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

        // load app and user
        $this->app  = $this->getApp($this->appId);
        $this->user = $this->getUser($this->userId);

        // check rate limit
        $this->rateService->assertLimit(
            $remoteIp,
            $this->context->get('fusio.routeId'),
            $this->app,
            $this->response
        );

        // log request
        $this->logId = $this->apiLogger->log(
            $this->context->get('fusio.routeId'),
            $this->appId,
            $this->userId,
            $remoteIp,
            $this->request
        );
    }

    public function getPreFilter()
    {
        $method = $this->getActiveMethod();
        $filter = array();

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        // cors header
        $allowOrigin = $this->configService->getValue('cors_allow_origin');
        if (!empty($allowOrigin)) {
            $filter[] = new CORS($allowOrigin);
        }

        // authorization is required if the method is not public. In case we get
        // a header from the client we also add the oauth2 filter so that the
        // client gets maybe another rate limit
        $authorization = $this->request->getHeader('Authorization');
        if (!$method['public'] || !empty($authorization)) {
            $filter[] = new Oauth2Filter(
                $this->connection,
                $this->request->getMethod(),
                $this->context->get('fusio.routeId'),
                $this->config->get('fusio_project_key'),
                function ($accessToken) {
                    $this->appId  = $accessToken['appId'];
                    $this->userId = $accessToken['userId'];
                }
            );
        }

        return $filter;
    }

    /**
     * Select all methods from the routes method table and build a resource
     * based on the data. If the route is in production mode read the schema
     * from the cache else resolve it
     *
     * @param integer $version
     * @return \PSX\Api\Resource|null
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
                return Transformer::toRecord($this->getBody() ?: new \stdClass());
            } else {
                return $this->getBodyAs($method->getRequest());
            }
        } else {
            return new Record();
        }
    }

    private function executeAction($record)
    {
        $method   = $this->getActiveMethod();
        $context  = new EngineContext($this->context->get('fusio.routeId'), $this->app, $this->user);
        $request  = new Request($this->request, $this->uriFragments, $this->getParameters(), $record);
        $response = null;
        $actionId = $method['action'];

        if ($actionId > 0) {
            if ($method['status'] != Resource::STATUS_DEVELOPMENT) {
                // if the method is not in dev mode we load the action from the
                // cache
                $repository = unserialize($method['actionCache']);

                if ($repository instanceof Repository\ActionInterface) {
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

    /**
     * @param integer $appId
     * @return \Fusio\Engine\Model\AppInterface
     */
    private function getApp($appId)
    {
        $app = $this->appRepository->get($appId);

        if (!$app instanceof Model\AppInterface) {
            $app = new Model\App();
            $app->setAnonymous(true);
            $app->setScopes([]);
        }

        return $app;
    }

    /**
     * @param integer $userId
     * @return \Fusio\Engine\Model\UserInterface
     */
    private function getUser($userId)
    {
        $user = $this->userRepository->get($userId);

        if (!$user instanceof Model\UserInterface) {
            $user = new Model\User();
            $user->setAnonymous(true);
        }

        return $user;
    }
}
