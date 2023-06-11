<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Model\ActionInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\Request\RpcRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Worker\ClientFactory;
use Fusio\Impl\Worker\Generated\App;
use Fusio\Impl\Worker\Generated\Context;
use Fusio\Impl\Worker\Generated\Execute;
use Fusio\Impl\Worker\Generated\Request;
use Fusio\Impl\Worker\Generated\RequestContext;
use Fusio\Impl\Worker\Generated\Result;
use Fusio\Impl\Worker\Generated\User;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use Thrift\Exception\TException;

/**
 * WorkerAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
abstract class WorkerAbstract extends ActionAbstract
{
    private ConfigInterface $config;

    public function __construct(RuntimeInterface $runtime, ConfigInterface $config)
    {
        parent::__construct($runtime);

        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $worker = $this->config->get('fusio_worker');
        $endpoint = $worker[$this->getLanguage()] ?? null;

        if (empty($endpoint)) {
            throw new StatusCode\InternalServerErrorException('It looks like there is no worker configured for the language: ' . $this->getLanguage() . '. Please add a worker to the configuration file, more information at: https://www.fusio-project.org/documentation/worker');
        }

        $action = $context->getAction();
        if (!$action instanceof ActionInterface) {
            throw new StatusCode\InternalServerErrorException('No action was provided');
        }

        $execute = new Execute();
        $execute->action = $action->getName();
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

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', $this->getLanguage(), ''));
    }

    abstract protected function getLanguage(): string;

    private function buildRequest(RequestInterface $request): Request
    {
        $return = new Request();
        $return->arguments = $request->getArguments();
        $return->payload = \json_encode($request->getPayload());
        $return->context = new RequestContext();

        $requestContext = $request->getContext();
        if ($requestContext instanceof HttpRequestContext) {
            $return->context->name = 'http';
        } elseif ($requestContext instanceof RpcRequestContext) {
            $return->context->name = 'rpc';
        } else {
            throw new StatusCode\BadRequestException('Received an not supported request');
        }

        return $return;
    }

    /**
     * @return array<string, string>
     */
    private function getHttpHeaders(\PSX\Http\RequestInterface $request): array
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
