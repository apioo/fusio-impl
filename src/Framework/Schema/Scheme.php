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

namespace Fusio\Impl\Framework\Schema;

/**
 * Scheme
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
enum Scheme: string
{
    case SCHEMA = 'schema';
    case HTTP = 'http';
    case HTTPS = 'https';
    case FILE = 'file';
    case TYPEHUB = 'typehub';

    public static function wrap(?string $schemaName): ?string
    {
        if (empty($schemaName)) {
            return null;
        }

        if (str_contains($schemaName, '://')) {
            return $schemaName;
        }

        return 'schema://' . $schemaName;
    }

    /**
     * @param string $schema
     * @return array{Scheme, string}
     */
    public static function split(string $schema): array
    {
        $pos = strpos($schema, '://');
        if ($pos === false) {
            return [self::SCHEMA, $schema];
        }

        $scheme = substr($schema, 0, $pos);
        $value = substr($schema, $pos + 3);

        return [self::from($scheme), $value];
    }
}
