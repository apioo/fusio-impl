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
use Mcp\Server\Transport\Http\HttpSession;
use Mcp\Server\Transport\Http\SessionStoreInterface;
use PSX\Json\Parser;
use PSX\Sql\Condition;

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

    public function load(string $sessionId): ?HttpSession
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $sessionId);

        $row = $this->mcpSessionTable->findOneBy($condition);
        if (!$row instanceof Table\Generated\McpSessionRow) {
            return null;
        }

        $data = Parser::decode($row->getData(), true);
        if (!is_array($data)) {
            return null;
        }

        return HttpSession::fromArray($data);
    }

    public function save(HttpSession $session): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $session->getId());

        $row = $this->mcpSessionTable->findOneBy($condition);
        if ($row instanceof Table\Generated\McpSessionRow) {
            $row->setData(Parser::encode($session->toArray()));
            $this->mcpSessionTable->update($row);
        } else {
            $row = new Table\Generated\McpSessionRow();
            $row->setTenantId($this->frameworkConfig->getTenantId());
            $row->setSessionId($session->getId());
            $row->setData(Parser::encode($session->toArray()));
            $this->mcpSessionTable->create($row);
        }
    }

    public function delete(string $sessionId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\McpSessionTable::COLUMN_SESSION_ID, $sessionId);

        $this->mcpSessionTable->deleteBy($condition);
    }
}
