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

namespace Fusio\Impl\Backend\Api\Action;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Entity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends BackendApiAbstract
{
    use ValidatorTrait;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Action
     */
    protected $actionService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Action_Entity_Path');
        $path->addInteger('action_id');

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.action']);
        $get->addResponse(200, $this->schemaManager->getSchema(Model\Action::class));

        $post = $builder->addMethod('PUT');
        $post->setSecurity(Authorization::BACKEND, ['backend.action']);
        $post->setRequest($this->schemaManager->getSchema(Model\Action_Update::class));
        $post->addResponse(200, $this->schemaManager->getSchema(Model\Message::class));

        $delete = $builder->addMethod('DELETE');
        $delete->setSecurity(Authorization::BACKEND, ['backend.action']);
        $delete->addResponse(200, $this->schemaManager->getSchema(Model\Message::class));

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $action = $this->tableManager->getTable(View\Action::class)->getEntity(
            (int) $context->getUriFragment('action_id')
        );

        if (!empty($action)) {
            if ($action['status'] == Table\Action::STATUS_DELETED) {
                throw new StatusCode\GoneException('Action was deleted');
            }

            return $action;
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->assertSandboxAccess($record);

        $this->actionService->update(
            (int) $context->getUriFragment('action_id'),
            $record,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Action successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->actionService->delete(
            (int) $context->getUriFragment('action_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Action successful deleted',
        );
    }
}
