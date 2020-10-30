<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Backend\Model\Schema_Create;
use Fusio\Impl\Backend\Model\Schema_Form;
use Fusio\Impl\Backend\Model\Schema_Update;
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
 * @link    http://fusio-project.org
 */
class Schema
{
    /**
     * @var \Fusio\Impl\Table\Schema
     */
    protected $schemaTable;

    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Impl\Schema\Parser
     */
    protected $schemaParser;

    /**
     * @var \Fusio\Impl\Schema\Loader
     */
    protected $schemaLoader;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Schema $schemaTable
     * @param \Fusio\Impl\Table\Route\Method $routesMethodTable
     * @param \Fusio\Impl\Schema\Parser $schemaParser
     * @param \Fusio\Impl\Schema\Loader $schemaLoader
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Schema $schemaTable, Table\Route\Method $routesMethodTable, Parser $schemaParser, Loader $schemaLoader, EventDispatcherInterface $eventDispatcher)
    {
        $this->schemaTable       = $schemaTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->schemaParser      = $schemaParser;
        $this->schemaLoader      = $schemaLoader;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create(Schema_Create $schema, UserContext $context)
    {
        if (!preg_match('/^[A-z0-9\-\_]{3,64}$/', $schema->getName())) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        // check whether schema exists
        if ($this->exists($schema->getName())) {
            throw new StatusCode\BadRequestException('Schema already exists');
        }

        // create schema
        $record = [
            'status' => Table\Schema::STATUS_ACTIVE,
            'name'   => $schema->getName(),
            'source' => $this->parseSource($schema->getSource()),
            'form'   => $this->parseForm($schema->getForm()),
        ];

        $this->schemaTable->create($record);

        $schemaId = $this->schemaTable->getLastInsertId();
        $schema->setId($schemaId);

        $this->eventDispatcher->dispatch(new CreatedEvent($schema, $context));

        return $schemaId;
    }

    public function update(int $schemaId, Schema_Update $schema, UserContext $context)
    {
        $existing = $this->schemaTable->get($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = [
            'id'     => $existing['id'],
            'name'   => $schema->getName(),
            'source' => $this->parseSource($schema->getSource()),
            'form'   => $this->parseForm($schema->getForm()),
        ];

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($schema, $existing, $context));
    }

    public function delete(int $schemaId, UserContext $context)
    {
        $existing = $this->schemaTable->get($schemaId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($existing['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Schema::STATUS_DELETED,
        ];

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function updateForm($schemaId, Schema_Form $form, UserContext $context)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = [
            'id'   => $schema['id'],
            'form' => $this->parseForm($form),
        ];

        $this->schemaTable->update($record);
    }

    public function generatePreview($schemaId)
    {
        $schema = $this->schemaLoader->getSchema($schemaId);
        if ($schema instanceof SchemaInterface) {
            return (new Generator\Html())->generate($schema);
        } else {
            throw new StatusCode\BadRequestException('Invalid schema');
        }
    }

    /**
     * Returns either false ot the id of the existing schema
     * 
     * @param string $name
     * @return integer|false
     */
    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Schema::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $connection = $this->schemaTable->getOneBy($condition);

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

    /**
     * @param \PSX\Schema\SchemaInterface|null $schema
     * @return string|null
     */
    public static function serializeCache(SchemaInterface $schema = null)
    {
        if ($schema === null) {
            return null;
        }

        return base64_encode(serialize($schema));
    }

    /**
     * @param string|null $data
     * @return \PSX\Schema\SchemaInterface|null
     */
    public static function unserializeCache($data)
    {
        if ($data === null) {
            return null;
        }

        return unserialize(base64_decode($data));
    }
}
