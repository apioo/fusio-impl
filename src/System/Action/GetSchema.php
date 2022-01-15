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

namespace Fusio\Impl\System\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Schema\Loader;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Generator;
use PSX\Sql\TableManagerInterface;

/**
 * GetSchema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GetSchema extends ActionAbstract
{
    private View\Schema $table;
    private Loader $loader;

    public function __construct(TableManagerInterface $tableManager, Loader $loader)
    {
        $this->table = $tableManager->getTable(View\Schema::class);
        $this->loader = $loader;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $schema = $this->table->getEntityWithForm(
            $request->get('name')
        );

        if (empty($schema)) {
            throw new StatusCode\NotFoundException('Could not find schema');
        }

        if ($schema['status'] == Table\Schema::STATUS_DELETED) {
            throw new StatusCode\GoneException('Schema was deleted');
        }

        $type = $this->loader->getSchema($schema['name']);
        $json = \json_decode((new Generator\TypeSchema())->generate($type));

        return [
            'schema' => $json,
            'form' => $schema['form'],
        ];
    }
}
