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

namespace Fusio\Impl\Backend\Api\Connection;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Record\RecordInterface;

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
     * @var \Fusio\Impl\Service\Connection
     */
    protected $connectionService;

    /**
     * @Inject
     * @var \Fusio\Engine\Parser\ParserInterface
     */
    protected $connectionParser;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Connection_Entity_Path');
        $path->addInteger('connection_id');

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.connection']);
        $get->addResponse(200, Model\Connection::class);

        $put = $builder->addMethod('PUT');
        $put->setSecurity(Authorization::BACKEND, ['backend.connection']);
        $put->setRequest(Model\Connection_Update::class);
        $put->addResponse(200, Model\Message::class);

        $delete = $builder->addMethod('DELETE');
        $delete->setSecurity(Authorization::BACKEND, ['backend.connection']);
        $delete->addResponse(200, Model\Message::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $connection = $this->tableManager->getTable(View\Connection::class)->getEntityWithConfig(
            (int) $context->getUriFragment('connection_id'),
            $this->config->get('fusio_project_key'),
            $this->connectionParser
        );

        if (!empty($connection)) {
            if ($connection['status'] == Table\Connection::STATUS_DELETED) {
                throw new StatusCode\GoneException('Connection was deleted');
            }

            return $connection;
        } else {
            throw new StatusCode\NotFoundException('Could not find connection');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->connectionService->update(
            (int) $context->getUriFragment('connection_id'),
            $record,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Connection successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->connectionService->delete(
            (int) $context->getUriFragment('connection_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Connection successful deleted',
        );
    }
}
