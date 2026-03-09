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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
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
        private Table\Action\Commit $actionCommitTable,
    ) {
    }

    public function commit(int $actionId, ?string $config, UserContext $context): ?string
    {
        if (empty($config)) {
            return null;
        }

        [$previousCommitHash, $previousConfigHash] = $this->actionCommitTable->findCurrentHash($actionId);

        $configHash = sha1($config);
        if ($configHash === $previousConfigHash) {
            return null;
        }

        $commitHash = sha1($context->getTenantId() . $context->getUserId() . $actionId . $previousCommitHash . $configHash);

        $existing = $this->actionCommitTable->findOneByCommitHash($commitHash);
        if ($existing instanceof Table\Generated\ActionCommitRow) {
            return null;
        }

        $row = new Table\Generated\ActionCommitRow();
        $row->setActionId($actionId);
        $row->setUserId($context->getUserId());
        $row->setPrevHash($previousCommitHash ?? '');
        $row->setCommitHash($commitHash);
        $row->setConfigHash($configHash);
        $row->setConfig($config);
        $row->setInsertDate(LocalDateTime::now());
        $this->actionCommitTable->create($row);

        return $commitHash;
    }
}
