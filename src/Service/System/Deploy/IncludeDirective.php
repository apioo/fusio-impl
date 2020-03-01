<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System\Deploy;

use PSX\Json\Pointer;
use PSX\Uri\Uri;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * IncludeDirective
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class IncludeDirective
{
    public static function resolve($data, $basePath, $type)
    {
        if (is_string($data) && substr($data, 0, 8) == '!include') {
            $file = new Uri(substr($data, 9));
            $path = $basePath . '/' . $file->getPath();

            if (is_file($path)) {
                $fragment = $file->getFragment();
                $data     = Yaml::parse(EnvProperties::replace(file_get_contents($path)));

                if (!empty($fragment)) {
                    $pointer = new Pointer($fragment);
                    return $pointer->evaluate($data);
                } else {
                    return $data;
                }
            } else {
                throw new RuntimeException('Could not resolve file: ' . $path);
            }
        } elseif (is_array($data)) {
            return $data;
        } else {
            throw new RuntimeException(ucfirst($type) . ' must be either an array or a string containing a "!include" directive');
        }
    }
}
