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

use Fusio\Adapter;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use PSX\Api\OperationInterface;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Method
{
    private string $action;
    private ?string $request;
    private array $responses;
    private ?string $parameters;
    private ?string $scope;
    private ?string $eventName;
    private bool $public;
    private ?int $costs;
    private ?string $operationId;
    private int $stability;

    public function __construct(string $action, ?string $request, array $responses, ?string $parameters = null, ?string $scope = null, ?string $eventName = null, bool $public = false, ?int $costs = null, ?string $operationId = null, int $stability = OperationInterface::STABILITY_STABLE)
    {
        $this->action = $action;
        $this->request = $request;
        $this->responses = $responses;
        $this->parameters = $parameters;
        $this->scope = $scope;
        $this->eventName = $eventName;
        $this->public = $public;
        $this->costs = $costs;
        $this->operationId = $operationId;
        $this->stability = $stability;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getParameters(): ?string
    {
        return $this->parameters;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function getCosts(): ?int
    {
        return $this->costs;
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function getStability(): int
    {
        return $this->stability;
    }
}
