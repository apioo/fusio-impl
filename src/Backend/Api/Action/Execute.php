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
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Framework\Exception\Converter;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Environment\HttpResponseInterface;

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
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Action_Execute_Path');
        $path->addInteger('action_id');

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::BACKEND, ['backend.action']);
        $post->setRequest($this->schemaManager->getSchema(Model\Action_Execute_Request::class));
        $post->addResponse(200, $this->schemaManager->getSchema(Model\Action_Execute_Response::class));

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        try {
            $response = $this->actionExecutorService->execute(
                (int) $context->getUriFragment('action_id'),
                $record
            );

            if ($response instanceof HttpResponseInterface) {
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
