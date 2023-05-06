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

namespace Fusio\Impl\Controller;

use Fusio\Engine\Record\PassthruRecord;
use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Action\Invoker;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Http\Filter\UserAgentEnforcer;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * ActionExecutor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ActionExecutor extends ControllerAbstract
{
    private Invoker $actionInvokerService;
    private ContextFactory $contextFactory;

    public function __construct(Invoker $actionInvokerService, ContextFactory $contextFactory)
    {
        $this->actionInvokerService = $actionInvokerService;
        $this->contextFactory = $contextFactory;
    }

    public function getPreFilter(): array
    {
        return [
            ...parent::getPreFilter(),
            UserAgentEnforcer::class,
            Filter\AssertMethod::class,
            Filter\Authentication::class,
            Filter\RequestLimit::class,
            Filter\Logger::class,
        ];
    }

    public function execute(...$arguments): mixed
    {
        $context = $this->contextFactory->getActive();

        $payload = new Record();
        if (isset($arguments['payload'])) {
            $payload = $arguments['payload'];
            if (!$payload instanceof RecordInterface) {
                $payload = PassthruRecord::fromPayload($payload);
            }
        }

        $requestContext = new Request\RpcRequest(__METHOD__);

        return $this->actionInvokerService->invoke(new Request($arguments, $payload, $requestContext), $context);
    }
}
