<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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
    case PHP_CLASS = 'php+class';
    case HTTP = 'http';
    case HTTPS = 'https';
    case FILE = 'file';
    case MIME = 'mime';
    case TYPEHUB = 'typehub';

    public static function wrap(?string $schemaName): ?string
    {
        if (empty($schemaName)) {
            return null;
        }

        if (str_contains($schemaName, '://')) {
            return $schemaName;
        }

        if (class_exists($schemaName)) {
            return 'php+class://' . $schemaName;
        }

        return 'schema://' . $schemaName;
    }

    /**
     * @param string $schema
     * @return array{Scheme, string, ?string}
     */
    public static function split(string $schema): array
    {
        $scheme = self::SCHEMA->value;
        if (str_contains($schema, '://')) {
            $parts = explode('://', $schema);
            $scheme = $parts[0];
            $schema = $parts[1];
        }

        $name = $schema;
        $hash = null;
        if (str_contains($name, '@')) {
            $parts = explode('@', $name);
            $name = $parts[0];
            $hash = $parts[1];
        }

        return [self::from($scheme), $name, $hash];
    }
}
