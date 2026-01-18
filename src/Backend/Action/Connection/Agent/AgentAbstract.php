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

namespace Fusio\Impl\Backend\Action\Connection\Agent;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\Connector;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Exception\BadRequestException;
use Symfony\AI\Agent\AgentInterface;

/**
 * AgentAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract readonly class AgentAbstract implements ActionInterface
{
    public function __construct(private Connector $connector, private FrameworkConfig $frameworkConfig)
    {
    }

    protected function getConnection(RequestInterface $request): AgentInterface
    {
        $connectionId = $request->get('connection_id');
        if (empty($connectionId)) {
            throw new BadRequestException('Provided no connection');
        }

        $connection = $this->connector->getConnection($connectionId);
        if (!$connection instanceof AgentInterface) {
            throw new BadRequestException('Provided an invalid connection');
        }

        return $connection;
    }

    protected function assertConnectionEnabled(): void
    {
        if (!$this->frameworkConfig->isConnectionEnabled()) {
            throw new StatusCode\ServiceUnavailableException('Database is not enabled, please change the setting "fusio_connection" at the configuration.php to "true" in order to activate the database');
        }
    }
}
