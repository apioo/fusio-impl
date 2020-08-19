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

namespace Fusio\Impl\Backend\Api\Rate;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Property;

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
     * @var \Fusio\Impl\Service\Rate
     */
    protected $rateService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());
        $resource->addPathParameter('rate_id', Property::getInteger());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.rate'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Rate::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend.rate'])
            ->setRequest($this->schemaManager->getSchema(Schema\Rate\Update::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->setSecurity(Authorization::BACKEND, ['backend.rate'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $rate = $this->tableManager->getTable(View\Rate::class)->getEntity(
            (int) $context->getUriFragment('rate_id')
        );

        if (!empty($rate)) {
            if ($rate['status'] == Table\Rate::STATUS_DELETED) {
                throw new StatusCode\GoneException('Rate was deleted');
            }

            return $rate;
        } else {
            throw new StatusCode\NotFoundException('Could not find rate');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->rateService->update(
            (int) $context->getUriFragment('rate_id'),
            $record->priority,
            $record->name,
            $record->rateLimit,
            $record->timespan,
            $record->allocation,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Rate successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->rateService->delete(
            (int) $context->getUriFragment('rate_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Rate successful deleted',
        );
    }
}
