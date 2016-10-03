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

namespace Fusio\Impl\Backend\Api\Action;

use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Authorization\ProtectionTrait;
use PSX\Api\Resource;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Loader\Context;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Validate\Filter as PSXFilter;

/**
 * Execute
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Execute extends SchemaApiAbstract
{
    use ProtectionTrait;

    /**
     * @Inject
     * @var \PSX\Schema\SchemaManagerInterface
     */
    protected $schemaManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Action\Executor
     */
    protected $actionExecutorService;

    /**
     * @Inject
     * @var \PSX\Framework\Exception\ConverterInterface
     */
    protected $exceptionConverter;

    /**
     * @param integer $version
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Action\Execute\Request'))
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Action\Execute\Response'))
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
                    'headers'    => $response->getHeaders(),
                    'body'       => $response->getBody(),
                );
            } else {
                return array(
                    'statusCode' => 204,
                    'headers'    => [],
                    'body'       => [],
                );
            }
        } catch (\Exception $e) {
            return array(
                'statusCode' => 500,
                'headers'    => [],
                'body'       => $this->exceptionConverter->convert($e),
            );
        }
    }
}
