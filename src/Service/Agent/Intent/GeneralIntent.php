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

/**
 * GeneralIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GeneralIntent implements IntentInterface
{
    public function getMessage(): string
    {
        return '';
    }

    public function getTools(): array
    {
        return [
            'backend_action_getAll',
            'backend_action_get',
            'backend_action_getClasses',
            'backend_action_getForm',
            'backend_action_execute',
            'backend_action_get',
            'backend_action_update',
            'backend_action_delete',
            'backend_connection_getAll',
            'backend_connection_get',
            'backend_connection_database_getTables',
            'backend_connection_database_getTable',
            'backend_connection_database_createTable',
            'backend_connection_database_updateTable',
            'backend_connection_database_deleteTable',
            'backend_connection_database_getRows',
            'backend_connection_database_getRow',
            'backend_connection_database_createRow',
            'backend_connection_database_updateRow',
            'backend_connection_database_deleteRow',
            'backend_connection_filesystem_getAll',
            'backend_connection_filesystem_get',
            'backend_connection_filesystem_create',
            'backend_connection_filesystem_update',
            'backend_connection_filesystem_delete',
            'backend_connection_http_execute',
            'backend_connection_sdk_get',
            'backend_cronjob_getAll',
            'backend_cronjob_create',
            'backend_cronjob_get',
            'backend_cronjob_update',
            'backend_cronjob_delete',
            'backend_event_getAll',
            'backend_event_create',
            'backend_event_get',
            'backend_event_update',
            'backend_event_delete',
            'backend_log_getAll',
            'backend_log_get',
            'backend_operation_getAll',
            'backend_operation_create',
            'backend_operation_get',
            'backend_operation_update',
            'backend_operation_delete',
            'backend_schema_getAll',
            'backend_schema_create',
            'backend_schema_get',
            'backend_schema_update',
            'backend_schema_delete',
            'backend_trigger_getAll',
            'backend_trigger_create',
            'backend_trigger_get',
            'backend_trigger_update',
            'backend_trigger_delete',
        ];
    }

    public function getResponseFormat(): ?array
    {
        return null;
    }
}
