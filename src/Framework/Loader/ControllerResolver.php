<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Framework\Loader;

use Fusio\Impl\Controller\ActionController;
use PSX\Framework\Loader\ControllerResolver as FrameworkControllerResolver;
use PSX\Framework\Loader\ControllerResolverInterface;

/**
 * ControllerResolver
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
        if (str_starts_with($source[0], 'operation://')) {
            return [$this->controller, 'execute'];
        } else {
            return $this->controllerResolver->resolve($source);
        }
    }
}
