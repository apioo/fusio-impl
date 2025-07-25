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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Schema\CreatedEvent;
use Fusio\Impl\Event\Schema\DeletedEvent;
use Fusio\Impl\Event\Schema\UpdatedEvent;
use Fusio\Impl\Framework\Schema\Scheme;
use Fusio\Impl\Table;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaForm;
use Fusio\Model\Backend\SchemaUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Record\RecordInterface;
use PSX\Schema\Generator;
use PSX\Schema\SchemaManagerInterface;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Schema
{
    public function __construct(
        private Table\Schema $schemaTable,
        private Schema\Validator $validator,
        private SchemaManagerInterface $schemaManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(SchemaCreate $schema, UserContext $context): int
    {
        $this->validator->assert($schema, $context->getTenantId());

        try {
            $this->schemaTable->beginTransaction();

            $row = new Table\Generated\SchemaRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Schema::STATUS_ACTIVE);
            $row->setName($schema->getName());
            $row->setSource($this->parseSource($schema->getSource()) ?? throw new StatusCode\BadRequestException('Schema source must not be empty'));
            $row->setMetadata($schema->getMetadata() !== null ? Parser::encode($schema->getMetadata()) : null);
            $this->schemaTable->create($row);

            $schemaId = $this->schemaTable->getLastInsertId();
            $schema->setId($schemaId);

            // check whether we can load the schema
            //$this->schemaManager->getSchema(Scheme::wrap($row->getName()));

            $this->schemaTable->commit();
        } catch (\Throwable $e) {
            $this->schemaTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($schema, $context));

        return $schemaId;
    }

    public function update(string $schemaId, SchemaUpdate $schema, UserContext $context): int
    {
        $existing = $this->schemaTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $this->validator->assert($schema, $context->getTenantId(), $existing);

        try {
            $this->schemaTable->beginTransaction();

            $existing->setName($schema->getName() ?? $existing->getName());
            $existing->setSource($this->parseSource($schema->getSource()) ?? $existing->getSource());
            $existing->setMetadata($schema->getMetadata() !== null ? Parser::encode($schema->getMetadata()) : $existing->getMetadata());
            $this->schemaTable->update($existing);

            // check whether we can load the schema
            $source = Scheme::wrap($existing->getName());
            $this->schemaManager->clear($source);
            $this->schemaManager->getSchema($source);

            $this->schemaTable->commit();
        } catch (\Throwable $e) {
            $this->schemaTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($schema, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $schemaId, UserContext $context): int
    {
        $existing = $this->schemaTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $existing->setStatus(Table\Schema::STATUS_DELETED);
        $this->schemaTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function generatePreview(string $schemaId): Generator\Code\Chunks|string
    {
        $schema = $this->schemaManager->getSchema(Scheme::wrap($schemaId));
        return (new Generator\Html())->generate($schema);
    }

    private function parseSource(?RecordInterface $source): ?string
    {
        if ($source instanceof RecordInterface) {
            $class = $source->get('$class');
            if (is_string($class)) {
                return $class;
            } else {
                return Parser::encode($source, JSON_PRETTY_PRINT);
            }
        } else {
            return null;
        }
    }
}
