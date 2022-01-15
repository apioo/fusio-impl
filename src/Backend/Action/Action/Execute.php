<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Action;
use Fusio\Model\Backend\Action_Execute_Request;
use PSX\Framework\Exception\Converter;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Execute extends ActionAbstract
{
    private Action\Executor $actionExecutorService;

    public function __construct(Action\Executor $actionExecutorService)
    {
        $this->actionExecutorService = $actionExecutorService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof Action_Execute_Request);

        try {
            $response = $this->actionExecutorService->execute(
                $request->get('action_id'),
                $body
            );

            if ($response instanceof HttpResponseInterface) {
                return [
                    'statusCode' => $response->getStatusCode(),
                    'headers'    => $response->getHeaders() ?: new \stdClass(),
                    'body'       => $response->getBody(),
                ];
            } else {
                return [
                    'statusCode' => 200,
                    'headers'    => new \stdClass(),
                    'body'       => $response,
                ];
            }
        } catch (\Throwable $e) {
            $exceptionConverter = new Converter(true);

            return [
                'statusCode' => 500,
                'headers'    => new \stdClass(),
                'body'       => $exceptionConverter->convert($e),
            ];
        }
    }
}
