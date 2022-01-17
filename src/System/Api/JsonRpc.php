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

use Fusio\Impl\Rpc\InvokerFactory;
use Fusio\Model\System;
use PSX\Api\Attribute\Description;
use PSX\Api\Attribute\Incoming;
use PSX\Api\Attribute\Outgoing;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Dependency\Attribute\Inject;
use PSX\Framework\Controller\ControllerAbstract;
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
class JsonRpc extends ControllerAbstract
{
    #[Inject]
    private InvokerFactory $rpcInvokerFactory;

    public function getPreFilter(): array
    {
        $filter = [];

        // it is required for every request to have an user agent which
        // identifies the client
        $filter[] = new UserAgentEnforcer();

        return $filter;
    }

    #[Description('JSON-RPC Endpoint please take a look at https://www.jsonrpc.org/specification')]
    #[Incoming(schema: Passthru::class)]
    #[Outgoing(code: 200, schema: System\Rpc_Response_Success::class)]
    protected function doPost($record, HttpContextInterface $context): mixed
    {
        $invoker = $this->rpcInvokerFactory->createByFramework($context);

        $server = new Server(function($method, $params) use ($invoker) {
            return $invoker->invoke($method, $params);
        });

        return $server->invoke($record);
    }
}
