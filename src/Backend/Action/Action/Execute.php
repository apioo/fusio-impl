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

namespace Fusio\Impl\Backend\Action\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Action_Create;
use Fusio\Impl\Backend\Model\Action_Execute_Request;
use Fusio\Impl\Backend\Model\Action_Update;
use Fusio\Impl\Service\Action;
use PSX\Framework\Exception\Converter;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Execute extends ActionAbstract
{
    /**
     * @var Action
     */
    private $actionExecutorService;

    public function __construct(Action\Executor $actionExecutorService)
    {
        $this->actionExecutorService = $actionExecutorService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $body = $request->getPayload();

        assert($body instanceof Action_Execute_Request);

        try {
            $response = $this->actionExecutorService->execute(
                (int) $request->get('action_id'),
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
                    'statusCode' => 204,
                    'headers'    => new \stdClass(),
                    'body'       => new \stdClass(),
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
