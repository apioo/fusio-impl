<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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
use Fusio\Impl\Table\Routes\Schema as TableRoutesSchema;
use Fusio\Impl\Table\Routes\Method as TableRoutesMethod;
use Fusio\Impl\Table\Schema as TableSchema;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Record\RecordInterface;
use PSX\Schema\SchemaInterface;
use PSX\Schema\Generator;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use RuntimeException;

/**
 * Schema
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
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
     * @var \Fusio\Impl\Table\Routes\Schema
     */
    protected $routesSchemaTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Engine\Schema\ParserInterface
     */
    protected $schemaParser;

    public function __construct(TableSchema $schemaTable, TableRoutesSchema $routesSchemaTable, TableRoutesMethod $routesMethodTable, ParserInterface $schemaParser)
    {
        $this->schemaTable       = $schemaTable;
        $this->routesSchemaTable = $routesSchemaTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->schemaParser      = $schemaParser;
    }

    public function getAll($startIndex = 0, $search = null, $routeId = null)
    {
        $condition = new Condition();

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        if (!empty($routeId)) {
            $sql = 'SELECT schemaId
                      FROM fusio_routes_schema
                     WHERE routeId = ?';

            $condition->raw('id IN (' . $sql . ')', [$routeId]);
        }

        return new ResultSet(
            $this->schemaTable->getCount($condition),
            $startIndex,
            16,
            $this->schemaTable->getAll(
                $startIndex, 
                16, 
                'id',
                Sql::SORT_DESC, 
                $condition, 
                Fields::blacklist(['propertyName', 'source', 'cache'])
            )
        );
    }

    public function get($schemaId)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (!empty($schema)) {
            return $schema;
        } else {
            throw new StatusCode\NotFoundException('Could not find schema');
        }
    }

    public function create($name, $source)
    {
        if (!preg_match('/^[A-z0-9\-\_]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid schema name');
        }

        // check whether schema exists
        $condition  = new Condition();
        $condition->equals('name', $name);

        $connection = $this->schemaTable->getOneBy($condition);

        if (!empty($connection)) {
            throw new StatusCode\BadRequestException('Connection already exists');
        }

        // create schema
        $this->schemaTable->create(array(
            'status' => TableSchema::STATUS_ACTIVE,
            'name'   => $name,
            'source' => $source,
            'cache'  => $this->schemaParser->parse(json_encode($source)),
        ));
    }

    public function update($schemaId, $name, $source)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (!empty($schema)) {
            $this->schemaTable->update(array(
                'id'     => $schema['id'],
                'name'   => $name,
                'source' => $source,
                'cache'  => $this->schemaParser->parse(json_encode($source)),
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find schema');
        }
    }

    public function delete($schemaId)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (!empty($schema)) {
            // check whether we have routes which depend on this schema
            if ($this->routesMethodTable->hasSchema($schemaId)) {
                throw new StatusCode\BadRequestException('Cannot delete schema because a route depends on it');
            }

            // delete route dependencies
            $this->routesSchemaTable->deleteBySchema($schema['id']);

            $this->schemaTable->delete(array(
                'id' => $schema['id']
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find schema');
        }
    }

    public function getHtmlPreview($schemaId)
    {
        $schema = $this->schemaTable->get($schemaId);

        if (!empty($schema)) {
            $generator = new Generator\Html();
            $schema    = unserialize($schema['cache']);

            if ($schema instanceof SchemaInterface) {
                return $generator->generate($schema);
            } else {
                throw new RuntimeException('Invalid schema');
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid schema id');
        }
    }
}
