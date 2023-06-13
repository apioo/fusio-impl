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

namespace Fusio\Impl\Factory;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Factory\Resolver;

/**
 * EngineDetector
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EngineDetector
{
    /**
     * This method determines the fitting engine for the provided string. The provided string gets modified in case it
     * has the uri format
     */
    public static function getEngine(string &$class): string
    {
        $engine = null;

        if (($pos = strpos($class, '://')) !== false) {
            $proto = substr($class, 0, $pos);

            switch ($proto) {
                case 'file':
                    $class  = substr($class, $pos + 3);
                    $engine = self::getEngineByFile($class);
                    break;

                case 'http':
                case 'https':
                    $engine = Resolver\HttpUrl::class;
                    break;

                default:
                    $class  = substr($class, $pos + 3);
                    $engine = self::getEngineByProto($proto);
            }
        } elseif (is_file($class)) {
            $engine = self::getEngineByFile($class);
        } elseif (class_exists($class)) {
            $engine = PhpClass::class;
        }

        if ($engine === null) {
            $engine = PhpClass::class;
        }

        return $engine;
    }

    private static function getEngineByFile(string $file): ?string
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        return match ($fileExtension) {
            'php'   => Resolver\PhpFile::class,
            default => Resolver\StaticFile::class,
        };
    }

    private static function getEngineByProto(string $proto): ?string
    {
        return match ($proto) {
            'PhpClass'   => PhpClass::class,
            'PhpFile'    => Resolver\PhpFile::class,
            'HttpUrl'    => Resolver\HttpUrl::class,
            'StaticFile' => Resolver\StaticFile::class,
            default      => null,
        };
    }
}
