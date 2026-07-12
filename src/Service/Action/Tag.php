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

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;

/**
 * The tag service freezes all action and schema configs to a specific version which can be used if the client provides
 * the version on the X-Api-Version header
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Tag
{
    public function __construct(
        private Table\Action\Commit $actionCommitTable,
        private Table\Action\Tag $actionTagTable,
        private Table\Schema\Commit $schemaCommitTable,
        private Table\Schema\Tag $schemaTagTable,
    ) {
    }

    public function tag(string $version, ContextInterface $context): void
    {
        $this->tagActionCommits($version, $context);
        $this->tagSchemaCommits($version, $context);
    }

    private function tagActionCommits(string $version, ContextInterface $context): void
    {
        $commitIds = $this->actionCommitTable->findAllLatestCommitIds();
        foreach ($commitIds as $commitId) {
            $row = new Table\Generated\ActionTagRow();
            $row->setCommitId($commitId);
            $row->setUserId($context->getUser()->getId());
            $row->setVersion($version);
            $row->setInsertDate(LocalDateTime::now());
            $this->actionTagTable->create($row);
        }
    }

    private function tagSchemaCommits(string $version, ContextInterface $context): void
    {
        $commitIds = $this->schemaCommitTable->findAllLatestCommitIds();
        foreach ($commitIds as $commitId) {
            $row = new Table\Generated\SchemaTagRow();
            $row->setCommitId($commitId);
            $row->setUserId($context->getUser()->getId());
            $row->setVersion($version);
            $row->setInsertDate(LocalDateTime::now());
            $this->schemaTagTable->create($row);
        }
    }
}
