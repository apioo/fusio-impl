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

namespace Fusio\Impl\Framework\Loader;

use Fusio\Impl\Controller\ActionController;
use PSX\Framework\Loader\ControllerResolver as FrameworkControllerResolver;
use PSX\Framework\Loader\ControllerResolverInterface;

/**
 * ControllerResolver
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ControllerResolver implements ControllerResolverInterface
{
    private ActionController $controller;
    private FrameworkControllerResolver $controllerResolver;

    public function __construct(ActionController $controller, FrameworkControllerResolver $controllerResolver)
    {
        $this->controller = $controller;
        $this->controllerResolver = $controllerResolver;
    }

    public function resolve(mixed $source): array
    {
        if (str_starts_with($source[0], 'operation:')) {
            return [$this->controller, 'execute'];
        } else {
            return $this->controllerResolver->resolve($source);
        }
    }
}
