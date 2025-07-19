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
    public function __construct(
        public string $action,
        public string $httpMethod,
        public string $httpPath,
        public int $httpCode,
        public string $outgoing,
        public array $parameters = [],
        public ?string $incoming = null,
        public array $throws = [],
        public ?string $eventName = null,
        public bool $public = false,
        public int $stability = OperationInterface::STABILITY_STABLE,
        public ?int $costs = null,
        public ?string $description = null,
    ) {
    }
}
