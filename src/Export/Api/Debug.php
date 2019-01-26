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

use Fusio\Impl\Export\Schema;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Schema\Passthru;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Debug
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Debug extends SchemaApiAbstract
{
    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Debug::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setRequest($this->schemaManager->getSchema(Passthru::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Debug::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setRequest($this->schemaManager->getSchema(Passthru::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Debug::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->setRequest($this->schemaManager->getSchema(Passthru::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Debug::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PATCH')
            ->setRequest($this->schemaManager->getSchema(Passthru::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Debug::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->mirror(null, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        return $this->mirror($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        return $this->mirror($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        return $this->mirror($record, $context);
    }

    /**
     * @inheritdoc
     */
    protected function doPatch($record, HttpContextInterface $context)
    {
        return $this->mirror($record, $context);
    }

    /**
     * @param object|null $record
     * @param HttpContextInterface $context
     * @return array
     */
    protected function mirror($record, HttpContextInterface $context)
    {
        return [
            'method' => $context->getMethod(),
            'headers' => $context->getHeaders(),
            'parameters' => $context->getParameters(),
            'body' => $record
        ];
    }
}
