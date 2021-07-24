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
use GuzzleHttp\Client;
use PSX\Framework\Config\Config;
use PSX\Http\Exception\InternalServerErrorException;

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

    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->httpClient = new Client();
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $worker = $this->config->get('fusio_worker');
        $endpoint = $worker[$this->getLanguage()] ?? null;

        if (empty($endpoint)) {
            throw new \RuntimeException('It looks like there is no worker configured for the language: ' . $this->getLanguage() . '. Please add a worker to the configuration file, more information at: https://www.fusio-project.org/documentation/worker');
        }

        $response = $this->httpClient->post($endpoint . '/execute', [
            'json' => $this->build($request, $context)
        ]);

        $data = \json_decode((string) $response->getBody());

        if (isset($data->success) && $data->success === false) {
            throw new InternalServerErrorException($data->message ?? 'An unknown error occurred at the worker');
        }

        if (isset($data->response)) {
            $statusCode = (int) ($data->response->statusCode ?? 200);
            $headers = (array) ($data->response->headers ?? []);
            $body = $data->response->body ?? new \stdClass();
        } else {
            throw new \RuntimeException('The worker does not return a response');
        }

        if (isset($data->events) && is_array($data->events)) {
            foreach ($data->events as $event) {
                $this->dispatcher->dispatch($event->eventName, $event->data ?? null);
            }
        }

        if (isset($data->logs) && is_array($data->logs)) {
            foreach ($data->logs as $log) {
                $this->logger->log($log->level, $log->message, $log->context ?? null);
            }
        }

        return $this->response->build($statusCode, $headers, $body);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', $this->getLanguage(), 'Click <a ng-click="help.showDialog(\'help/action/worker.md\')">here</a> for more information.'));
    }

    abstract protected function getLanguage(): string;

    private function build(RequestInterface $request, ContextInterface $context): array
    {
        return [
            'action' => $context->getAction()->getName(),
            'request' => $this->buildRequest($request),
            'context' => $this->buildContext($context)
        ];
    }

    private function buildRequest(RequestInterface $request): array
    {
        if ($request instanceof HttpInterface) {
            return [
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'uriFragments' => (object) $request->getUriFragments(),
                'parameters' => (object) $request->getParameters(),
                'body' => $request->getBody(),
            ];
        } elseif ($request instanceof RpcInterface) {
            return [
                'arguments' => $request->getArguments()
            ];
        } else {
            return [];
        }
    }

    private function buildContext(ContextInterface $context): array
    {
        $app = $context->getApp();
        $user = $context->getUser();

        return [
            'routeId' => $context->getRouteId(),
            'baseUrl' => $context->getBaseUrl(),
            'app' => [
                'id' => $app->getId(),
                'userId' => $app->getUserId(),
                'status' => $app->getStatus(),
                'name' => $app->getName(),
                'url' => $app->getUrl(),
                'appKey' => $app->getAppKey(),
                'scopes' => $app->getScopes(),
                'parameters' => $app->getParameters(),
            ],
            'user' => [
                'id' => $user->getId(),
                'roleId' => $user->getRoleId(),
                'categoryId' => $user->getCategoryId(),
                'status' => $user->getStatus(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'points' => $user->getPoints(),
            ],
        ];
    }
}
