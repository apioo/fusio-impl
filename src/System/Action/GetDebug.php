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

namespace Fusio\Impl\System\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\Request\RpcRequest;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Consumer\View;
use PSX\Framework\Config\Config;
use PSX\Sql\TableManagerInterface;

/**
 * GetDebug
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GetDebug extends ActionAbstract
{
    /**
     * @var View\User
     */
    private $table;

    /**
     * @var Config
     */
    private $config;

    public function __construct(TableManagerInterface $tableManager, Config $config)
    {
        $this->table = $tableManager->getTable(View\User::class);
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $data = [
            'class' => get_class($request)
        ];

        if ($request instanceof HttpRequest) {
            $data = array_merge($data, $this->buildHttp($request));
        } elseif ($request instanceof RpcRequest) {
            $data = array_merge($data, $this->buildRpc($request));
        }

        return $data;
    }

    private function buildHttp(HttpRequest $request)
    {
        return [
            'method' => $request->getMethod(),
            'uriFragments' => $request->getUriFragments(),
            'parameters' => $request->getParameters(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody(),
        ];
    }

    private function buildRpc(RpcRequest $request)
    {
        return [
            'arguments' => $request->getArguments(),
        ];
    }
}
