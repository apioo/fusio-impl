<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use PSX\Api\Resource;
use PSX\Framework\Exception\Converter;
use PSX\Framework\Loader\Context;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Execute extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Action\Executor
     */
    protected $actionExecutorService;

    /**
     * @param integer $version
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->setRequest($this->schemaManager->getSchema(Schema\Action\Execute\Request::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Action\Execute\Response::class))
        );

        return $resource;
    }

    /**
     * Returns the POST response
     *
     * @param \PSX\Record\RecordInterface $record
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doPost($record)
    {
        try {
            $response = $this->actionExecutorService->execute(
                (int) $this->getUriFragment('action_id'),
                $record->method,
                $record->uriFragments,
                $record->parameters,
                $record->headers,
                $record->body
            );

            if ($response instanceof ResponseInterface) {
                return array(
                    'statusCode' => $response->getStatusCode(),
                    'headers'    => $response->getHeaders() ?: new \stdClass(),
                    'body'       => $response->getBody(),
                );
            } else {
                return array(
                    'statusCode' => 204,
                    'headers'    => new \stdClass(),
                    'body'       => new \stdClass(),
                );
            }
        } catch (\Throwable $e) {
            $exceptionConverter = new Converter(true);

            return array(
                'statusCode' => 500,
                'headers'    => new \stdClass(),
                'body'       => $exceptionConverter->convert($e),
            );
        }
    }
}
