<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\System\Api;

use Fusio\Model\System;
use PSX\Api\DocumentedInterface;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Framework\Controller\SchemaApiAbstract;
use PSX\Framework\Schema\Passthru;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Json\Rpc\Server;

/**
 * JsonRpc
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class JsonRpc extends SchemaApiAbstract implements DocumentedInterface
{
    /**
     * @Inject
     * @var \Fusio\Impl\Rpc\InvokerFactory
     */
    protected $rpcInvokerFactory;

    /**
     * @return array
     */
    public function getPreFilter()
    {
        $filter = [];

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        return $filter;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $post = $builder->addMethod('POST');
        $post->setDescription('JSON-RPC Endpoint please take a look at https://www.jsonrpc.org/specification');
        $post->setRequest(Passthru::class);
        $post->addResponse(200, System\Rpc_Response_Success::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $invoker = $this->rpcInvokerFactory->createByFramework($this->request);
        $server  = new Server($invoker);

        return $server->invoke($record);
    }
}
