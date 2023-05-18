<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Worker\Generated\RpcRequest;
use PSX\Http\Exception as StatusCode;

/**
 * InspectAction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InspectAction implements ActionInterface
{
    public function getName(): string
    {
        return 'Inspect-Action';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $throw = $request->get('throw');
        if ($throw) {
            throw new StatusCode\InternalServerErrorException('Foobar');
        }

        $data = [];
        $context = $request->getContext();
        if ($context instanceof HttpRequest) {
            $data = [
                'method' => $context->getMethod(),
                'headers' => $context->getHeaders(),
                'uri_fragments' => $context->getUriFragments(),
                'parameters' => $context->getParameters(),
            ];
        } elseif ($context instanceof RpcRequest) {
            $data = [
                'name' => $context->getName(),
            ];
        }

        return $this->response->build(200, [], [
            'arguments' => $request->getArguments(),
            'payload' => $request->getPayload(),
            'context' => $data
        ]);
    }
}
