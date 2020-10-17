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

namespace Fusio\Impl\System\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\Request\RpcRequest;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Rpc\InvokerFactory;

/**
 * Invoke
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Invoke extends ActionAbstract
{
    /**
     * @var InvokerFactory
     */
    private $invokerFactory;

    public function __construct(InvokerFactory $invokerFactory)
    {
        $this->invokerFactory = $invokerFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        if ($request instanceof HttpRequest) {
            $invoker = $this->invokerFactory->createByEngine($request);
            return $invoker->invoke($request->getUriFragment('method'), $request->getPayload());
        } else {
            throw new \RuntimeException('Invalid request type');
        }
    }
}
