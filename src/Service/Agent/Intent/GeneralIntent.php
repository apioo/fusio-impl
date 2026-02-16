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

namespace Fusio\Impl\Service\Agent\Intent;

use Fusio\Impl\Service\Agent\IntentInterface;
use Fusio\Impl\Service\Agent\Serializer\ResultSerializer;
use Fusio\Impl\Table\Generated\AgentRow;
use Fusio\Model\Backend\AgentMessage;
use Symfony\AI\Platform\Result\ResultInterface;

/**
 * GeneralIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GeneralIntent implements IntentInterface
{
    public function __construct(private ResultSerializer $resultSerializer)
    {
    }

    public function getMessage(): string
    {
        return '';
    }

    public function getTools(): array
    {
        return [
            // api
            'backend_operation_getAll',
            'backend_operation_get',
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_execute',
            'backend_action_get',
            'backend_schema_getAll',
            'backend_schema_get',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_database_getRows',
            'backend_connection_database_getRow',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
            'backend_event_getAll',
            'backend_event_get',
            'backend_cronjob_getAll',
            'backend_cronjob_get',
            'backend_trigger_getAll',
            'backend_trigger_get',

            // consumer
            'backend_app_getAll',
            'backend_app_get',
            'backend_scope_getAll',
            'backend_scope_get',
            'backend_user_getAll',
            'backend_user_get',
            'backend_rate_getAll',
            'backend_rate_get',

            // analytics
            'backend_log_getAll',
            'backend_log_get',
            'backend_log_getAllErrors',
            'backend_log_getError',
            'backend_token_getAll',
            'backend_token_get',

            // plan
            'backend_plan_getAll',
            'backend_plan_get',
            'backend_transaction_getAll',
            'backend_transaction_get',
        ];
    }

    public function getResponseSchema(): ?array
    {
        return null;
    }

    public function transformResult(ResultInterface $result): AgentMessage
    {
        return $this->resultSerializer->serialize($result);
    }

    public function onMessagePersisted(AgentRow $row, AgentMessage $message): void
    {
    }
}
