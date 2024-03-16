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

namespace Fusio\Impl\Service\Webhook;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Webhook;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Event $eventTable;
    private Table\User $userTable;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\Event $eventTable, Table\User $userTable, UsageLimiter $usageLimiter)
    {
        $this->eventTable = $eventTable;
        $this->userTable = $userTable;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(Webhook $webhook, ?string $tenantId, ?Table\Generated\WebhookRow $existing = null): void
    {
        $this->usageLimiter->assertWebhookCount($tenantId);

        $eventId = $webhook->getEventId();
        if ($eventId !== null) {
            $this->assertEvent($eventId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Webhook event must not be empty');
            }
        }

        $userId = $webhook->getUserId();
        if ($userId !== null) {
            $this->assertUser($userId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Webhook user must not be empty');
            }
        }

        $this->assertUrl($webhook->getEndpoint());
    }

    private function assertEvent(int $eventId, ?string $tenantId): void
    {
        $event = $this->eventTable->findOneByTenantAndId($tenantId, $eventId);
        if (empty($event)) {
            throw new StatusCode\BadRequestException('Webhook event does not exist');
        }
    }

    private function assertUser(int $userId, ?string $tenantId): void
    {
        $user = $this->userTable->findOneByTenantAndId($tenantId, $userId);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Webhook user does not exist');
        }
    }

    private function assertUrl(?string $url): void
    {
        if (empty($url)) {
            throw new StatusCode\BadRequestException('Webhook endpoint must contain a value');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('Webhook endpoint must be a valid url');
        }
    }
}
