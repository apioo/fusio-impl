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

namespace Fusio\Impl\Consumer\Action\Identity;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use Fusio\Model;
use PSX\Framework\Http\Writer\Template;
use PSX\Framework\Loader\ReverseRouter;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Exchange
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Exchange implements ActionInterface
{
    private Service\Identity $identity;
    private ReverseRouter $reverseRouter;

    public function __construct(Service\Identity $identity, ReverseRouter $reverseRouter)
    {
        $this->identity = $identity;
        $this->reverseRouter = $reverseRouter;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $error = $request->get('error');
        $errorDescription = $request->get('error_description');
        $errorUri = $request->get('error_uri');

        if (!empty($error)) {
            throw new StatusCode\BadRequestException('Could not authenticate user: ' . implode(' - ', array_filter([$error, $errorDescription, $errorDescription, $errorUri])));
        }

        $code = $request->get('code');
        if (empty($code)) {
            throw new StatusCode\BadRequestException('No code provided');
        }

        $state = $request->get('state');
        if (empty($state)) {
            throw new StatusCode\BadRequestException('No state provided');
        }

        $token = $this->identity->exchange($request->get('identity'), $code, $state);

        // normally the exchange method throws a redirect exception but in case we have no redirect we simply show the
        // access token to the user
        return $this->renderToken($token);
    }

    private function renderToken(?AccessToken $token): Template
    {
        $data = [
            'access_token' => $token->getAccessToken(),
            'expires_in' => $token->getExpiresIn(),
            'refresh_token' => $token->getRefreshToken(),
            'scope' => $token->getScope(),
        ];

        $templateFile = __DIR__ . '/../../../../resources/template/token.php';
        return new Template($data, $templateFile, $this->reverseRouter);
    }
}
