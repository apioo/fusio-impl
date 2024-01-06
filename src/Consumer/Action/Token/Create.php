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

namespace Fusio\Impl\Consumer\Action\Token;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\App\Token;
use Fusio\Impl\Service\Scope;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\BadRequestException;

/**
 * Create
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Create implements ActionInterface
{
    private Token $tokenService;
    private Scope $scopeService;
    private string $expireToken;

    public function __construct(Token $tokenService, Scope $scopeService, ConfigInterface $config)
    {
        $this->tokenService = $tokenService;
        $this->scopeService = $scopeService;
        $this->expireToken = $config->get('fusio_expire_token');
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof TokenCreate);


        $expires = match ($body->getExpires()) {
            1 => 'P30D',
            2 => 'P1w',
            3 => 'P1M',
            default => $this->expireToken,
        };

        // scopes
        $scopes = $this->scopeService->getValidScopes($body->getScopes(), $body->getAppId(), $context->getUser()->getId());
        if (empty($scopes)) {
            throw new BadRequestException('No valid scope given');
        }

        $token = $this->tokenService->generateAccessToken(
            $body->getAppId(),
            $context->getUser()->getId(),
            $scopes,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($expires),
        );

        return new HttpResponse(201, [], [
            'success' => true,
            'message' => 'Token successfully created',
            'token'   => $token,
        ]);
    }
}
