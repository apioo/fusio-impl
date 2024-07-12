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

namespace Fusio\Impl\Command;

use Symfony\Component\Console\Input\InputInterface;

/**
 * TypeSafeTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
trait TypeSafeTrait
{
    public function getArgumentAsString(InputInterface $input, string $name): string
    {
        $value = $input->getArgument($name);
        if (empty($value)) {
            throw new \RuntimeException('Provided no value for ' . $name);
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            throw new \RuntimeException('Provided an invalid value for ' . $name);
        }

        return $value;
    }

    public function getOptionalArgumentAsString(InputInterface $input, string $name): ?string
    {
        $value = $input->getArgument($name);
        if (empty($value)) {
            return null;
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            throw new \RuntimeException('Provided an invalid value for ' . $name);
        }

        return $value;
    }
}
