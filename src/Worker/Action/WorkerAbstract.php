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

namespace Fusio\Impl\Worker\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpInterface;
use Fusio\Engine\Request\RpcInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Worker\ClientFactory;
use Fusio\Impl\Worker\Generated\App;
use Fusio\Impl\Worker\Generated\Context;
use Fusio\Impl\Worker\Generated\Execute;
use Fusio\Impl\Worker\Generated\HttpRequest;
use Fusio\Impl\Worker\Generated\Request;
use Fusio\Impl\Worker\Generated\Result;
use Fusio\Impl\Worker\Generated\RpcRequest;
use Fusio\Impl\Worker\Generated\User;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use Thrift\Exception\TException;

/**
 * WorkerAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class WorkerAbstract extends ActionAbstract
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $worker = $this->config->get('fusio_worker');
        $endpoint = $worker[$this->getLanguage()] ?? null;

        if (empty($endpoint)) {
            throw new StatusCode\InternalServerErrorException('It looks like there is no worker configured for the language: ' . $this->getLanguage() . '. Please add a worker to the configuration file, more information at: https://www.fusio-project.org/documentation/worker');
        }

        $execute = new Execute();
        $execute->action = $context->getAction()->getName();
        $execute->request = $this->buildRequest($request);
        $execute->context = $this->buildContext($context);

        try {
            $result = ClientFactory::getClient($endpoint, $this->getLanguage())->executeAction($execute);
        } catch (TException $e) {
            throw new StatusCode\ServiceUnavailableException('Could not execute action: ' . $e->getMessage(), $e);
        }

        if (!$result instanceof Result) {
            throw new StatusCode\ServiceUnavailableException('Worker returned no result');
        }

        if (!empty($result->events)) {
            foreach ($result->events as $event) {
                $this->dispatcher->dispatch($event->eventName, \json_decode($event->data));
            }
        }

        if (!empty($result->logs)) {
            foreach ($result->logs as $log) {
                $this->logger->log($log->level, $log->message);
            }
        }

        return $this->response->build(
            $result->response->statusCode ?? 200,
            $result->response->headers ?? [],
            \json_decode($result->response->body)
        );
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', $this->getLanguage(), 'Click <a ng-click="help.showDialog(\'help/action/worker-' . $this->getLanguage() . '.md\')">here</a> for more information.'));
    }

    abstract protected function getLanguage(): string;

    private function buildRequest(RequestInterface $request)
    {
        $return = new Request();
        if ($request instanceof HttpInterface) {
            $httpRequest = new HttpRequest();
            $httpRequest->method = $request->getMethod();
            $httpRequest->headers = $this->getHttpHeaders($request);
            $httpRequest->uriFragments = $request->getUriFragments();
            $httpRequest->parameters = $request->getParameters();
            $httpRequest->body = \json_encode($request->getBody());
            $return->http = $httpRequest;
        } elseif ($request instanceof RpcInterface) {
            $rpcRequest = new RpcRequest();
            $rpcRequest->arguments = \json_encode($request->getArguments());
            $return->rpc = $rpcRequest;
        } else {
            throw new StatusCode\BadRequestException('Received an not supported request');
        }

        return $return;
    }

    private function getHttpHeaders(HttpInterface $request): array
    {
        $result = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $values) {
            $result[$name] = implode(', ', $values);
        }

        return $result;
    }

    private function buildContext(ContextInterface $context): Context
    {
        $app = new App();
        $app->id = $context->getApp()->getId();
        $app->userId = $context->getApp()->getUserId();
        $app->status = $context->getApp()->getStatus();
        $app->name = $context->getApp()->getName();
        $app->url = $context->getApp()->getUrl();
        $app->appKey = $context->getApp()->getAppKey();
        $app->scopes = $context->getApp()->getScopes();
        $app->parameters = $context->getApp()->getParameters();

        $user = new User();
        $user->id = $context->getUser()->getId();
        $user->roleId = $context->getUser()->getRoleId();
        $user->categoryId = $context->getUser()->getCategoryId();
        $user->status = $context->getUser()->getStatus();
        $user->name = $context->getUser()->getName();
        $user->email = $context->getUser()->getEmail();
        $user->points = $context->getUser()->getPoints();

        $return = new Context();
        $return->routeId = $context->getRouteId();
        $return->baseUrl = $context->getBaseUrl();
        $return->app = $app;
        $return->user = $user;

        return $return;
    }
}
