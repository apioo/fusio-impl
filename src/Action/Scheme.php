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

namespace Fusio\Impl\Action;

/**
 * Scheme
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
enum Scheme: string
{
    case ACTION = 'action';
    case PHP_CLASS = 'class';
    case CLI = 'cli';
    case FCGI = 'fcgi';
    case FILE = 'file';
    case GRAPHQL = 'graphql';
    case HTTP = 'http';
    case PHP = 'php';

    public static function wrap(?string $actionName): ?string
    {
        if (empty($actionName)) {
            return null;
        }

        if (str_contains($actionName, '://')) {
            return $actionName;
        }

        return 'action://' . $actionName;
    }

    /**
     * @param string $action
     * @return array{Scheme, string}
     */
    public static function split(string $action): array
    {
        $pos = strpos($action, '://');
        if ($pos === false) {
            return [self::ACTION, $action];
        }

        $scheme = substr($action, 0, $pos);
        $value = substr($action, $pos + 3);

        return [self::from($scheme), $value];
    }
}
