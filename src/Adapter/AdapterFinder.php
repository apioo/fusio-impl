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

namespace Fusio\Impl\Adapter;

use Fusio\Engine\AdapterInterface;

/**
 * AdapterFinder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AdapterFinder
{
    public static function getFiles(string $providerFile): array
    {
        $adapterClasses = include $providerFile;

        $files = [];
        foreach ($adapterClasses as $class) {
            if (!class_exists($class)) {
                throw new \RuntimeException('Provided an invalid adapter class ' . $class);
            }

            $adapter = new $class();
            if (!$adapter instanceof AdapterInterface) {
                throw new \RuntimeException('Provided adapter class must be an instance of ' . AdapterInterface::class);
            }

            $files[] = $adapter->getContainerFile();
        }

        return $files;
    }
}
