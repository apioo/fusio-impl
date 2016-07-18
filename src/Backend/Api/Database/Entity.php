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

namespace Fusio\Impl\Backend\Api\Database;

use Fusio\Impl\Authorization\ProtectionTrait;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Loader\Context;
use PSX\Http\Exception as StatusCode;
use PSX\Validate\Validate;

/**
 * Entity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends SchemaApiAbstract
{
    use ProtectionTrait;

    /**
     * @Inject
     * @var \PSX\Schema\SchemaManagerInterface
     */
    protected $schemaManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Database
     */
    protected $databaseService;

    /**
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Database\Table'))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Database\Table'))
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Database\Message'))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Database\Message'))
        );

        return $resource;
    }

    /**
     * Returns the GET response
     *
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doGet()
    {
        return $this->databaseService->getTable(
            (int) $this->getUriFragment('connection_id'),
            $this->getUriFragment('table')
        );
    }

    /**
     * Returns the PUT response
     *
     * @param \PSX\Record\RecordInterface $record
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doPut($record)
    {
        $queries = $this->databaseService->update(
            (int) $this->getUriFragment('connection_id'),
            $this->getUriFragment('table'),
            $record->columns,
            $record->indexes ?: [],
            $record->foreignKeys ?: [],
            $this->getParameter('preview', Validate::TYPE_BOOLEAN)
        );

        return array(
            'success' => true,
            'message' => 'Table successful updated',
            'queries' => $queries,
        );
    }

    /**
     * Returns the DELETE response
     *
     * @param \PSX\Record\RecordInterface $record
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doDelete($record)
    {
        $queries = $this->databaseService->delete(
            (int) $this->getUriFragment('connection_id'),
            $this->getUriFragment('table'),
            $this->getParameter('preview', Validate::TYPE_BOOLEAN)
        );

        return array(
            'success' => true,
            'message' => 'Table successful deleted',
            'queries' => $queries,
        );
    }
}
