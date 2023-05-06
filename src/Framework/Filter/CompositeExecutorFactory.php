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

namespace Fusio\Impl\Framework\Filter;

use Fusio\Impl\Controller\ActionController;
use PSX\Framework\Filter\ControllerExecutorFactory;
use PSX\Framework\Filter\ControllerExecutorFactoryInterface;
use PSX\Framework\Loader\Context;
use PSX\Http\FilterInterface;

/**
 * CompositeExecutorFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CompositeExecutorFactory implements ControllerExecutorFactoryInterface
{
    private ActionExecutorFactory $actionExecutorFactory;
    private ControllerExecutorFactory $controllerExecutorFactory;

    public function __construct(ActionExecutorFactory $actionExecutorFactory, ControllerExecutorFactory $controllerExecutorFactory)
    {
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->controllerExecutorFactory = $controllerExecutorFactory;
    }

    public function factory(object $controller, string $methodName, Context $context): FilterInterface
    {
        if ($controller::class === ActionController::class) {
            return $this->actionExecutorFactory->factory($controller, $methodName, $context);
        } else {
            return $this->controllerExecutorFactory->factory($controller, $methodName, $context);
        }
    }
}
