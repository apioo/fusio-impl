<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Model\Backend\Schema_Create;
use Fusio\Model\Backend\Schema_Form;
use Fusio\Model\Backend\Schema_Update;
use Fusio\Impl\Event\Schema\CreatedEvent;
use Fusio\Impl\Event\Schema\DeletedEvent;
use Fusio\Impl\Event\Schema\UpdatedEvent;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Schema\Parser;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;
use PSX\Schema\Generator;
use PSX\Schema\SchemaInterface;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private Table\Route\Method $routesMethodTable;
    private Loader $schemaLoader;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Schema $schemaTable, Table\Route\Method $routesMethodTable, Loader $schemaLoader, EventDispatcherInterface $eventDispatcher)
    {
        $this->schemaTable       = $schemaTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->schemaLoader      = $schemaLoader;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create(int $categoryId, Schema_Create $schema, UserContext $context): int
    {
        if (!preg_match('/^[A-z0-9\-\_]{3,64}$/', $schema->getName())) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        // check whether schema exists
        if ($this->exists($schema->getName())) {
            throw new StatusCode\BadRequestException('Schema already exists');
        }

        try {
            $this->schemaTable->beginTransaction();

            // create schema
            $record = new Table\Generated\SchemaRow([
                'category_id' => $categoryId,
                'status'      => Table\Schema::STATUS_ACTIVE,
                'name'        => $schema->getName(),
                'source'      => $this->parseSource($schema->getSource()),
                'form'        => $this->parseForm($schema->getForm()),
            ]);

            $this->schemaTable->create($record);

            $schemaId = $this->schemaTable->getLastInsertId();
            $schema->setId($schemaId);

            // check whether we can load the schema
            $this->schemaLoader->getSchema($schema->getName());

            $this->schemaTable->commit();
        } catch (\Throwable $e) {
            $this->schemaTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($schema, $context));

        return $schemaId;
    }

    public function update(int $schemaId, Schema_Update $schema, UserContext $context): int
    {
        $existing = $this->schemaTable->find($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        try {
            $this->schemaTable->beginTransaction();

            $record = new Table\Generated\SchemaRow([
                'id'     => $existing['id'],
                'name'   => $schema->getName(),
                'source' => $this->parseSource($schema->getSource()),
                'form'   => $this->parseForm($schema->getForm()),
            ]);

            $this->schemaTable->update($record);

            // check whether we can load the schema
            $this->schemaLoader->getSchema($schema->getName());

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

        if ($existing['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = new Table\Generated\SchemaRow([
            'id'     => $existing['id'],
            'status' => Table\Schema::STATUS_DELETED,
        ]);

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $schemaId;
    }

    public function updateForm(int $schemaId, Schema_Form $form, UserContext $context): void
    {
        $schema = $this->schemaTable->find($schemaId);
        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = new Table\Generated\SchemaRow([
            'id'   => $schema['id'],
            'form' => $this->parseForm($form),
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
        $condition  = new Condition();
        $condition->equals('status', Table\Schema::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $connection = $this->schemaTable->findOneBy($condition);

        if (!empty($connection)) {
            return $connection['id'];
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
