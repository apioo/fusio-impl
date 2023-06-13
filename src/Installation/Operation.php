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

namespace Fusio\Impl\Installation;

use PSX\Api\OperationInterface;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Operation
{
    public string $action;
    public string $httpMethod;
    public string $httpPath;
    public int $httpCode;
    public string $outgoing;
    public array $parameters;
    public ?string $incoming;
    public array $throws;
    public ?string $eventName;
    public bool $public;
    public int $stability;
    public ?int $costs;

    public function __construct(string $action, string $httpMethod, string $httpPath, int $httpCode, string $outgoing, array $parameters = [], ?string $incoming = null, array $throws = [], ?string $eventName = null, bool $public = false, int $stability = OperationInterface::STABILITY_STABLE, ?int $costs = null)
    {
        $this->action = $action;
        $this->httpMethod = $httpMethod;
        $this->httpPath = $httpPath;
        $this->httpCode = $httpCode;
        $this->outgoing = $outgoing;
        $this->parameters = $parameters;
        $this->incoming = $incoming;
        $this->throws = $throws;
        $this->eventName = $eventName;
        $this->public = $public;
        $this->stability = $stability;
        $this->costs = $costs;
    }
}
