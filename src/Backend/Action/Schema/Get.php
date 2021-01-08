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

namespace Fusio\Impl\Backend\Action\Schema;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\SchemaManagerInterface;
use PSX\Sql\TableManagerInterface;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Get extends ActionAbstract
{
    /**
     * @var View\Schema
     */
    private $table;

    /**
     * @var Loader
     */
    private $schemaManager;

    public function __construct(TableManagerInterface $tableManager, SchemaManagerInterface $schemaManager)
    {
        $this->table = $tableManager->getTable(View\Schema::class);
        $this->schemaManager = $schemaManager;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $schema = $this->table->getEntity(
            $request->get('schema_id')
        );

        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $source = $schema['source'];
        $readonly = false;
        if (strpos($source, '{') === false) {
            $source = (object) ['$class' => $source];
            $readonly = true;
        } else {
            $source = \json_decode($source);
        }

        $schema['source'] = $source;
        $schema['readonly'] = $readonly;

        return $schema;
    }
}
