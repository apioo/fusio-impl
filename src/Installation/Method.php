<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Installation;

use Fusio\Adapter;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use PSX\Api\Resource;

/**
 * Method
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
    private int $status;

    public function __construct(string $action, ?string $request, array $responses, ?string $parameters = null, ?string $scope = null, ?string $eventName = null, bool $public = false, ?int $costs = null, ?string $operationId = null, int $status = Resource::STATUS_ACTIVE)
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
        $this->status = $status;
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

    public function getStatus(): int
    {
        return $this->status;
    }
}
