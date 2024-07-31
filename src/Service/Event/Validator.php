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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Event;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Exception\ParserException;
use PSX\Schema\SchemaManagerInterface;

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
    private SchemaManagerInterface $schemaManager;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\Event $eventTable, SchemaManagerInterface $schemaManager, UsageLimiter $usageLimiter)
    {
        $this->eventTable = $eventTable;
        $this->schemaManager = $schemaManager;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(Event $event, ?string $tenantId, ?Table\Generated\EventRow $existing = null): void
    {
        $this->usageLimiter->assertEventCount($tenantId);

        $name = $event->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Event name must not be empty');
        }

        $schema = $event->getSchema();
        if ($schema !== null) {
            $this->assertSchema($schema);
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\EventRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid event name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->eventTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Event already exists');
        }
    }

    private function assertSchema(string $schema): void
    {
        try {
            $this->schemaManager->getSchema(SchemaScheme::wrap($schema));
        } catch (InvalidSchemaException|ParserException $e) {
            throw new StatusCode\BadRequestException('Schema "' . $schema . '" does not exist', $e);
        }
    }
}
