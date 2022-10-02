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

namespace Fusio\Impl\Console;

use Symfony\Component\Console\Input\InputInterface;

/**
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
trait InputTrait
{
    public function getArgumentString(InputInterface $input, string $name, ?string $default = null): string
    {
        $value = $input->getArgument($name);
        if (empty($value)) {
            if ($default !== null) {
                return $default;
            } else {
                throw new \InvalidArgumentException('You need to provide an argument ' . $name);
            }
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Provided argument ' . $name . ' must be a string');
        }

        return $value;
    }

    public function getOptionString(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        if (empty($value)) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Provided option ' . $name . ' must be a string');
        }

        return $value;
    }
}
