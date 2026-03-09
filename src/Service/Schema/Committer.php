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

namespace Fusio\Impl\Service\Schema;

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
        private Table\Schema\Commit $schemaCommitTable,
    ) {
    }

    public function commit(int $schemaId, string $source, UserContext $context): ?string
    {
        if (empty($source)) {
            return null;
        }

        [$previousCommitHash, $previousSourceHash] = $this->schemaCommitTable->findCurrentHash($schemaId);

        $sourceHash = sha1($source);
        if ($sourceHash === $previousSourceHash) {
            return null;
        }

        $commitHash = sha1($context->getTenantId() . $context->getUserId() . $schemaId . $previousCommitHash . $sourceHash);

        $existing = $this->schemaCommitTable->findOneByCommitHash($commitHash);
        if ($existing instanceof Table\Generated\SchemaCommitRow) {
            return null;
        }

        $row = new Table\Generated\SchemaCommitRow();
        $row->setSchemaId($schemaId);
        $row->setUserId($context->getUserId());
        $row->setPrevHash($previousCommitHash ?? '');
        $row->setCommitHash($commitHash);
        $row->setSourceHash($sourceHash);
        $row->setSource($source);
        $row->setInsertDate(LocalDateTime::now());
        $this->schemaCommitTable->create($row);

        return $commitHash;
    }
}
