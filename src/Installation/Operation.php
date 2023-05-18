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

use PSX\Api\OperationInterface;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

    public function __construct(string $action, string $httpMethod, string $httpPath, int $httpCode, string $outgoing, array $parameters = [], ?string $incoming = null, array $throws = [], ?string $eventName = null, bool $public = false, int $stability = OperationInterface::STABILITY_STABLE)
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
    }
}
