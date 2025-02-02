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

namespace Fusio\Impl\Action;

use Fusio\Engine\Inflection\ClassName;
use PSX\Http\Exception as StatusCode;

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
    case PHP_CLASS = 'php+class';
    case HTTP = 'http';
    case HTTPS = 'https';
    case FILE = 'file';

    public static function wrap(?string $actionName): ?string
    {
        if (empty($actionName)) {
            return null;
        }

        if (str_contains($actionName, '://')) {
            return self::buildAction($actionName);
        }

        if (class_exists($actionName)) {
            return self::buildAction('php+class://' . $actionName);
        }

        return self::buildAction('action://' . $actionName);
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

    private static function buildAction(string $actionName): string
    {
        if (!filter_var($actionName, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('Provided an invalid action url, must be in the format i.e. action://my_action_name');
        }

        [$scheme, $value] = self::split($actionName);

        if (empty($value)) {
            throw new StatusCode\BadRequestException('Provided action url contains an empty value');
        }

        switch ($scheme) {
            case self::ACTION:
                if (!preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $value)) {
                    throw new StatusCode\BadRequestException('Provided action url contains an invalid action name');
                }
                break;
            case self::PHP_CLASS:
                $value = ClassName::unserialize($value);
                if (!class_exists($value)) {
                    throw new StatusCode\BadRequestException('Provided action url contains a not existing PHP class');
                }
                $value = ClassName::serialize($value);
                break;
            case self::HTTP:
            case self::HTTPS:
                $value = str_replace(['http://', 'https://'], '', $value);
                if (str_contains($value, '://')) {
                    throw new StatusCode\BadRequestException('Provided action url must _not_ contain a scheme');
                }
                break;
            case self::FILE:
                if (!is_file($value)) {
                    throw new StatusCode\BadRequestException('Provided action url contains a not existing file');
                }
                break;
        }

        return $scheme->value . '://' . $value;
    }
}
