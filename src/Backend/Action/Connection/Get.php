<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Connection;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Provider\ConnectionProvider;
use Fusio\Impl\Service\Connection\Token;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Get implements ActionInterface
{
    private View\Connection $view;
    private FrameworkConfig $frameworkConfig;
    private ConnectionProvider $connectionParser;
    private Token $tokenService;

    public function __construct(View\Connection $view, FrameworkConfig $frameworkConfig, ConnectionProvider $connectionParser, Token $tokenService)
    {
        $this->view = $view;
        $this->frameworkConfig = $frameworkConfig;
        $this->connectionParser = $connectionParser;
        $this->tokenService = $tokenService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $connection = $this->view->getEntityWithConfig(
            $request->get('connection_id'),
            $this->frameworkConfig->getProjectKey(),
            $this->connectionParser,
            $context
        );

        if (empty($connection)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($connection['status'] == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        if ($this->tokenService->isValid($request->get('connection_id'))) {
            // in case the connection supports the oauth2 flow we add a button to start the authorization code flow
            $connection['oauth2'] = true;
        }

        return $connection;
    }
}
