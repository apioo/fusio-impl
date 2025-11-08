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

namespace Fusio\Impl\Messenger;

use Fusio\Engine\ContextInterface;

/**
 * TriggerEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class TriggerEvent
{
    public function __construct(
        private ?string $tenantId,
        private string $eventName,
        private mixed $payload,
        private ?int $userId,
        private ?ContextInterface $context = null
    ) {
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }
}
