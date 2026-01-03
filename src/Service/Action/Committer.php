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

namespace Fusio\Impl\Service\Action;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\UserContext;
use PSX\DateTime\LocalDateTime;

/**
 * Committer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Committer
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function commit(int $actionId, string $config, UserContext $context): void
    {
        $prevHash = (string) $this->connection->fetchOne('SELECT commit_hash FROM fusio_action_commit WHERE action_id = :action_id ORDER BY id DESC', [
            'action_id' => $actionId,
        ]);

        $now = LocalDateTime::now();
        $hash = sha1($actionId . $context->getUserId() . $prevHash . $config . $now->toString());

        $this->connection->insert('fusio_action_commit', [
            'action_id' => $actionId,
            'user_id' => $context->getUserId(),
            'prev_hash' => $prevHash,
            'commit_hash' => $hash,
            'config' => $config,
            'insert_date' => $now,
        ]);
    }
}
