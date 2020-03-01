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

use Fusio\Impl\Backend\Schema;
use RuntimeException;

/**
 * NameGenerator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class NameGenerator
{
    public static function getActionNameFromSource($source)
    {
        if (is_string($source)) {
            // remove scheme if uri format
            if (($pos = strpos($source, '://')) !== false) {
                $source = substr($source, $pos + 3);
            }

            if (is_file($source)) {
                $source = realpath($source);
                $source = substr($source, strlen(realpath(PSX_PATH_SRC)) + 1);
            }

            return preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $source);
        } else {
            throw new RuntimeException('Invalid action source ' . $source);
        }
    }

    public static function getSchemaNameFromSource($source)
    {
        if (is_string($source)) {
            if (substr($source, 0, 8) == '!include') {
                $source = trim(substr($source, 9));
                $source = str_replace('\\', '/', $source);
                $source = str_replace('resources/schema/', '', $source);
                $source = str_replace('.json', '', $source);
                $source = str_replace(' ', '-', ucwords(str_replace('/', ' ', $source)));

                return $source;
            } elseif (preg_match('/' . Schema\Schema::NAME_PATTERN . '/', $source)) {
                return $source;
            } else {
                return self::getNameFromJsonSchema($source);
            }
        } elseif (is_array($source)) {
            return self::getNameFromJsonSchema(json_encode($source));
        } else {
            throw new RuntimeException('Schema should be a string containing an "!include" directive pointing to a JsonSchema file');
        }
    }

    private static function getNameFromJsonSchema($schema)
    {
        $data = json_decode($schema);

        if (!$data instanceof \stdClass) {
            throw new RuntimeException('Schema must be a valid json schema');
        }

        if (isset($data->title) && is_string($data->title)) {
            return preg_replace('/[^A-z0-9\-\_]/', '_', $data->title);
        }

        // at last fallback we can only use the md5 of the schema as name
        return 'Schema-' . substr(md5($schema), 0, 8);
    }
}
