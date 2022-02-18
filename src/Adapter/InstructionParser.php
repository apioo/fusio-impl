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

namespace Fusio\Impl\Adapter;

use Fusio\Impl\Provider\ProviderConfig;
use stdClass;

/**
 * InstructionParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InstructionParser
{
    public function parse(stdClass $definition): array
    {
        $instructions = array();

        // get provider classes
        $types = ProviderConfig::getTypes();
        foreach ($types as $type) {
            $key = $type . 'Class';
            if (isset($definition->{$key}) && is_array($definition->{$key})) {
                foreach ($definition->{$key} as $class) {
                    $instructions[] = new Instruction\ProviderClass($class, $type);
                }
            }
        }

        return $instructions;
    }
}
