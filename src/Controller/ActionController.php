<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Controller;

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Action\Invoker;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Filter\UserAgentEnforcer;

/**
 * ActionController
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActionController extends ControllerAbstract
{
    private Invoker $actionInvokerService;

    public function __construct(Invoker $actionInvokerService)
    {
        $this->actionInvokerService = $actionInvokerService;
    }

    public function getPreFilter(): array
    {
        return [
            ...parent::getPreFilter(),
            UserAgentEnforcer::class,
            Filter\Tenant::class,
            Filter\Operation::class,
            Filter\Authentication::class,
            Filter\RequestLimit::class,
            Filter\Logger::class,
        ];
    }

    public function execute(Request $request, Context $context): mixed
    {
        return $this->actionInvokerService->invoke($request, $context);
    }
}
