<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Authorization\Action;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Revoke
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Revoke implements ActionInterface
{
    private Service\Token $tokenService;
    private Table\Token $table;
    private Service\System\ContextFactory $contextFactory;

    public function __construct(Service\Token $tokenService, Table\Token $table, Service\System\ContextFactory $contextFactory)
    {
        $this->tokenService = $tokenService;
        $this->table = $table;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $requestContext = $request->getContext();
        if ($requestContext instanceof HttpRequestContext) {
            $token = $this->getTokenByHttp($requestContext);
        } else {
            $token = $request->get('token');
        }

        if (empty($token)) {
            throw new StatusCode\BadRequestException('No token provided');
        }

        $row = $this->table->getTokenByToken($context->getTenantId(), $token);

        // the token must be assigned to the user
        if ($row instanceof Table\Generated\TokenRow && $row->getUserId() == $context->getUser()->getId()) {
            $this->tokenService->remove($row->getId(), $this->contextFactory->newActionContext($context));

            return [
                'success' => true
            ];
        } else {
            throw new StatusCode\BadRequestException('Invalid token');
        }
    }

    private function getTokenByHttp(HttpRequestContext $requestContext): ?string
    {
        $header = $requestContext->getRequest()->getHeader('Authorization');
        $parts  = explode(' ', $header, 2);
        $type   = $parts[0] ?? null;
        $token  = $parts[1] ?? null;

        if ($type !== 'Bearer') {
            throw new StatusCode\BadRequestException('Invalid token type');
        }

        return $token;
    }
}
