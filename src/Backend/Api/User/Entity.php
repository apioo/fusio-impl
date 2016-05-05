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

namespace Fusio\Impl\Backend\Api\User;

use Fusio\Impl\Authorization\ProtectionTrait;
use Fusio\Impl\Table\User;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Loader\Context;
use PSX\Http\Exception as StatusCode;

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
    use ValidatorTrait;

    /**
     * @Inject
     * @var \PSX\Schema\SchemaManagerInterface
     */
    protected $schemaManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\User'))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\User\Update'))
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Message'))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Message'))
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
        return $this->userService->get(
            (int) $this->getUriFragment('user_id')
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
        $this->userService->update(
            (int) $this->getUriFragment('user_id'),
            $record->status,
            $record->name,
            $record->scopes
        );

        return array(
            'success' => true,
            'message' => 'User successful updated',
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
        $this->userService->delete(
            (int) $this->getUriFragment('user_id')
        );

        return array(
            'success' => true,
            'message' => 'User successful deleted',
        );
    }
}
