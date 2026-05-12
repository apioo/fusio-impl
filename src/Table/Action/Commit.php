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

namespace Fusio\Impl\Table\Action;

use Fusio\Impl\Table\Generated;

/**
 * Commit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Commit extends Generated\ActionCommitTable
{
    /**
     * @return array{?string, ?string}
     */
    public function findCurrentHash(int $actionId): array
    {
        $row = $this->connection->fetchAssociative('SELECT commit_hash, config_hash FROM fusio_action_commit WHERE action_id = :action_id ORDER BY id DESC', [
            'action_id' => $actionId,
        ]);

        if (empty($row)) {
            return [null, null];
        }

        return [$row['commit_hash'], $row['config_hash']];
    }
}
