<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Export\Api;

use Fusio\Impl\Export\Schema as ExportSchema;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Schema\Generator;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\SchemaInterface;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Schema extends SchemaApiAbstract
{
    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Routes\Method
     */
    protected $routesMethodService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->addResponse(200, $this->schemaManager->getSchema(ExportSchema\Schema::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $schema = $this->tableManager->getTable(View\Schema::class)->getEntityWithForm(
            $context->getUriFragment('name')
        );

        if (!empty($schema)) {
            if ($schema['status'] == Table\Schema::STATUS_DELETED) {
                throw new StatusCode\GoneException('Schema was deleted');
            }

            $generator   = new Generator\JsonSchema();
            $schemaCache = unserialize($schema['cache']);

            if ($schemaCache instanceof SchemaInterface) {
                $json = \json_decode($generator->generate($schemaCache));
            } else {
                $json = null;
            }

            return [
                'schema' => $json,
                'form' => $schema['form'],
            ];
        } else {
            throw new StatusCode\NotFoundException('Could not find schema');
        }
    }
}
