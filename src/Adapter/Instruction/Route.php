<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Adapter\Instruction;

use Fusio\Impl\Adapter\InstructionAbstract;

/**
 * Route
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Route extends InstructionAbstract
{
    public function getName()
    {
        return 'Route';
    }

    public function getKey()
    {
        return 'routes';
    }

    public function getDescription()
    {
        $path   = isset($this->payload->path)   ? $this->payload->path   : null;
        $config = isset($this->payload->config) ? $this->payload->config : null;

        $usedMethods = [];
        if (is_array($config)) {
            foreach ($config as $version) {
                $methods = (array) $version->methods;
                $usedMethods = array_merge($usedMethods, array_keys($methods));
            }
        }

        return implode(', ', array_unique($usedMethods)) . ' ' . $path;
    }

    public function setBasePath($basePath)
    {
        $parts = explode('/', $basePath . '/' . $this->payload->path);
        $parts = array_filter($parts);

        $this->payload->path = '/' . implode('/', $parts);
    }
}
