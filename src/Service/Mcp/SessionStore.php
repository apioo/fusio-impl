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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Server\Session\SessionStoreInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Sql\Condition;
use Symfony\Component\Uid\Uuid;

/**
 * SessionStore
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class SessionStore implements SessionStoreInterface
{
    public function __construct(private Table\McpSession $mcpSessionTable, private FrameworkConfig $frameworkConfig)
    {
    }

    public function exists(Uuid $id): bool
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $id->toString());

        return $this->mcpSessionTable->getCount($condition) > 0;
    }

    public function read(Uuid $id): string|false
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $id->toString());

        $row = $this->mcpSessionTable->findOneBy($condition);
        if (!$row instanceof Table\Generated\McpSessionRow) {
            return false;
        }

        return $row->getData();
    }

    public function write(Uuid $id, string $data): bool
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $id->toString());

        $row = $this->mcpSessionTable->findOneBy($condition);
        if ($row instanceof Table\Generated\McpSessionRow) {
            $row->setData($data);
            $row->setUpdateDate(LocalDateTime::now());
            $this->mcpSessionTable->update($row);
        } else {
            $row = new Table\Generated\McpSessionRow();
            $row->setTenantId($this->frameworkConfig->getTenantId());
            $row->setSessionId($id->toString());
            $row->setData($data);
            $row->setUpdateDate(LocalDateTime::now());
            $row->setInsertDate(LocalDateTime::now());
            $this->mcpSessionTable->create($row);
        }

        return true;
    }

    public function destroy(Uuid $id): bool
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $id->toString());

        $this->mcpSessionTable->deleteBy($condition);

        return true;
    }

    public function gc(): array
    {
        return [];
    }
}
