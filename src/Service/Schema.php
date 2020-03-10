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

use Fusio\Engine\Schema\ParserInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Schema\CreatedEvent;
use Fusio\Impl\Event\Schema\DeletedEvent;
use Fusio\Impl\Event\Schema\UpdatedEvent;
use Fusio\Impl\Event\SchemaEvents;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Generator;
use PSX\Schema\SchemaInterface;
use PSX\Sql\Condition;
use RuntimeException;
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
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Engine\Schema\ParserInterface
     */
    protected $schemaParser;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Schema $schemaTable
     * @param \Fusio\Impl\Table\Routes\Method $routesMethodTable
     * @param \Fusio\Engine\Schema\ParserInterface $schemaParser
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Schema $schemaTable, Table\Routes\Method $routesMethodTable, ParserInterface $schemaParser, EventDispatcherInterface $eventDispatcher)
    {
        $this->schemaTable       = $schemaTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->schemaParser      = $schemaParser;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create($name, $source, UserContext $context)
    {
        if (!preg_match('/^[A-z0-9\-\_]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        // check whether schema exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Connection already exists');
        }

        // create schema
        $record = [
            'status' => Table\Schema::STATUS_ACTIVE,
            'name'   => $name,
            'source' => $source,
            'cache'  => $this->schemaParser->parse(json_encode($source)),
        ];

        $this->schemaTable->create($record);

        $schemaId = $this->schemaTable->getLastInsertId();

        $this->eventDispatcher->dispatch(new CreatedEvent($schemaId, $record, $context), SchemaEvents::CREATE);
        
        return $schemaId;
    }

    public function update($schemaId, $name, $source, $form, UserContext $context)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $record = [
            'id'     => $schema['id'],
            'name'   => $name,
            'source' => $source,
            'form'   => $form,
            'cache'  => $this->schemaParser->parse(json_encode($source)),
        ];

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($schemaId, $record, $schema, $context), SchemaEvents::UPDATE);
    }

    public function delete($schemaId, UserContext $context)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        // check whether we have routes which depend on this schema
        if ($this->routesMethodTable->hasSchema($schemaId)) {
            throw new StatusCode\BadRequestException('Cannot delete schema because a route depends on it');
        }

        $record = [
            'id'     => $schema['id'],
            'status' => Table\Schema::STATUS_DELETED,
        ];

        $this->schemaTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($schemaId, $schema, $context), SchemaEvents::DELETE);
    }

    public function updateForm($schemaId, $form, UserContext $context)
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
            'form' => $form,
        ];

        $this->schemaTable->update($record);
    }

    public function getHtmlPreview($schemaId)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (!empty($schema)) {
            $generator = new Generator\Html();
            $schema    = self::unserializeCache($schema['cache']);

            if ($schema instanceof SchemaInterface) {
                return $generator->generate($schema);
            } else {
                throw new RuntimeException('Invalid schema');
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid schema id');
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
