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

namespace Fusio\Impl\Backend\Api\Cronjob;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Framework\Loader\Context;
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
     * @var \Fusio\Impl\Service\Cronjob
     */
    protected $cronjobService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Cronjob::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->setRequest($this->schemaManager->getSchema(Schema\Cronjob\Update::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $cronjob = $this->tableManager->getTable(View\Cronjob::class)->getEntity(
            (int) $context->getUriFragment('cronjob_id')
        );

        if (!empty($cronjob)) {
            return $cronjob;
        } else {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->cronjobService->update(
            (int) $context->getUriFragment('cronjob_id'),
            $record->name,
            $record->cron,
            $record->action,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Cronjob successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->cronjobService->delete(
            (int) $context->getUriFragment('cronjob_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Cronjob successful deleted',
        );
    }
}
