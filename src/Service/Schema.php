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
use Fusio\Impl\Schema\Parser;
use Fusio\Impl\Service\Schema\Loader;
use Fusio\Impl\Table;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaForm;
use Fusio\Model\Backend\SchemaUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;
use PSX\Schema\Generator;
use PSX\Sql\Condition;

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
    private Table\Operation $operationTable;
    private Loader $schemaLoader;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Schema $schemaTable, Table\Operation $operationTable, Loader $schemaLoader, EventDispatcherInterface $eventDispatcher)
    {
        $this->schemaTable     = $schemaTable;
        $this->operationTable  = $operationTable;
        $this->schemaLoader    = $schemaLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, SchemaCreate $schema, UserContext $context): int
    {
        $name = $schema->getName();
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        // check whether schema exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Schema already exists');
        }

        // create schema
        try {
            $this->schemaTable->beginTransaction();

            $record = new Table\Generated\SchemaRow([
                Table\Generated\SchemaTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\SchemaTable::COLUMN_STATUS => Table\Schema::STATUS_ACTIVE,
                Table\Generated\SchemaTable::COLUMN_NAME => $name,
                Table\Generated\SchemaTable::COLUMN_SOURCE => $this->parseSource($schema->getSource()),
                Table\Generated\SchemaTable::COLUMN_FORM => $this->parseForm($schema->getForm()),
                Table\Generated\SchemaTable::COLUMN_METADATA => $schema->getMetadata() !== null ? json_encode($schema->getMetadata()) : null,
            ]);

            $this->schemaTable->create($record);

            $schemaId = $this->schemaTable->getLastInsertId();
            $schema->setId($schemaId);

            // check whether we can load the schema
            $this->schemaLoader->getSchema($name);

            $this->schemaTable->commit();
        } catch (\Throwable $e) {
            $this->schemaTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($schema, $context));

        return $schemaId;
    }

    public function update(int $schemaId, SchemaUpdate $schema, UserContext $context): int
    {
        $existing = $this->schemaTable->find($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $name = $schema->getName();
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        try {
            $this->schemaTable->beginTransaction();

            $record = new Table\Generated\SchemaRow([
                Table\Generated\SchemaTable::COLUMN_ID => $existing->getId(),
                Table\Generated\SchemaTable::COLUMN_NAME => $name,
                Table\Generated\SchemaTable::COLUMN_SOURCE => $this->parseSource($schema->getSource()),
                Table\Generated\SchemaTable::COLUMN_FORM => $this->parseForm($schema->getForm()),
                Table\Generated\SchemaTable::COLUMN_METADATA => $schema->getMetadata() !== null ? json_encode($schema->getMetadata()) : null,
            ]);

            $this->schemaTable->update($record);

            // check whether we can load the schema
            $this->schemaLoader->getSchema($name);

            $this->schemaTable->commit();
        } catch (\Throwable $e) {
            $this->schemaTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($schema, $existing, $context));

        return $schemaId;
    }

    public function delete(int $schemaId, UserContext $context): int
    {
        $existing = $this->schemaTable->find($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing->getStatus() == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = new Table\Generated\SchemaRow([
            Table\Generated\SchemaTable::COLUMN_ID => $existing->getId(),
            Table\Generated\SchemaTable::COLUMN_STATUS => Table\Schema::STATUS_DELETED,
        ]);

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $schemaId;
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

        $record = new Table\Generated\SchemaRow([
            Table\Generated\SchemaTable::COLUMN_ID => $schema->getId(),
            Table\Generated\SchemaTable::COLUMN_FORM => $this->parseForm($form),
        ]);

        $this->schemaTable->update($record);
    }

    public function generatePreview(int $schemaId): Generator\Code\Chunks|string
    {
        $schema = $this->schemaLoader->getSchema($schemaId);
        return (new Generator\Html())->generate($schema);
    }

    /**
     * Returns either false ot the id of the existing schema
     */
    public function exists(string $name): int|false
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\SchemaTable::COLUMN_STATUS, Table\Schema::STATUS_ACTIVE);
        $condition->equals(Table\Generated\SchemaTable::COLUMN_NAME, $name);

        $schema = $this->schemaTable->findOneBy($condition);

        if ($schema instanceof Table\Generated\SchemaRow) {
            return $schema->getId();
        } else {
            return false;
        }
    }

    private function parseSource(?RecordInterface $source): ?string
    {
        if ($source instanceof RecordInterface) {
            $class = $source->getProperty('$class');
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
