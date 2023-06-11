<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
use PSX\Record\RecordInterface;
use PSX\Schema\Generator;
use PSX\Schema\SchemaManagerInterface;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Schema
{
    private Table\Schema $schemaTable;
    private Schema\Validator $validator;
    private SchemaManagerInterface $schemaManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Schema $schemaTable, Schema\Validator $validator, SchemaManagerInterface $schemaManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->schemaTable = $schemaTable;
        $this->validator = $validator;
        $this->schemaManager = $schemaManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, SchemaCreate $schema, UserContext $context): int
    {
        $this->validator->assert($schema);

        try {
            $this->schemaTable->beginTransaction();

            $row = new Table\Generated\SchemaRow();
            $row->setCategoryId($categoryId);
            $row->setStatus(Table\Schema::STATUS_ACTIVE);
            $row->setName($schema->getName());
            $row->setSource($this->parseSource($schema->getSource()));
            $row->setForm($this->parseForm($schema->getForm()));
            $row->setMetadata($schema->getMetadata() !== null ? json_encode($schema->getMetadata()) : null);
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
        $existing = $this->schemaTable->findOneByIdentifier($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $this->validator->assert($schema, $existing);

        try {
            $this->schemaTable->beginTransaction();

            $existing->setName($schema->getName() ?? $existing->getName());
            $existing->setSource($this->parseSource($schema->getSource()) ?? $existing->getSource());
            $existing->setForm($this->parseForm($schema->getForm()) ?? $existing->getForm());
            $existing->setMetadata($schema->getMetadata() !== null ? json_encode($schema->getMetadata()) : $existing->getMetadata());
            $this->schemaTable->update($existing);

            // check whether we can load the schema
            $this->schemaManager->getSchema(Scheme::wrap($existing->getName()));

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
        $existing = $this->schemaTable->findOneByIdentifier($schemaId);
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

    public function updateForm(int $schemaId, SchemaForm $form, UserContext $context): void
    {
        $schema = $this->schemaTable->find($schemaId);
        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $schema->setForm($this->parseForm($form));
        $this->schemaTable->update($schema);
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
                return \json_encode($source, JSON_PRETTY_PRINT);
            }
        } else {
            return null;
        }
    }

    private function parseForm(?RecordInterface $source): ?string
    {
        if ($source instanceof RecordInterface) {
            return \json_encode($source, JSON_PRETTY_PRINT);
        } else {
            return null;
        }
    }
}
