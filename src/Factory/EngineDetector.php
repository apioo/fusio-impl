<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Factory;

use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Factory\Resolver;

/**
 * EngineDetector
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EngineDetector
{
    /**
     * This method determines the fitting engine for the provided string. The
     * provided string gets modified in case it has the uri format 
     * 
     * @param string $class
     * @return string
     */
    public static function getEngine(&$class)
    {
        $engine = null;

        if (($pos = strpos($class, '://')) !== false) {
            $proto = substr($class, 0, $pos);

            switch ($proto) {
                case 'file':
                    $class  = substr($class, $pos + 3);
                    $engine = self::getEngineByFile($class);
                    break;

                case 'file+php':
                    $class  = substr($class, $pos + 3);
                    $engine = Resolver\PhpFile::class;
                    break;

                case 'file+js':
                    $class  = substr($class, $pos + 3);
                    $engine = Resolver\JavascriptFile::class;
                    break;

                case 'php':
                    $class  = substr($class, $pos + 3);
                    $engine = PhpClass::class;
                    break;

                case 'http':
                case 'https':
                    $engine = Resolver\HttpUrl::class;
                    break;
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

    /**
     * @param string $file
     * @return string|null
     */
    private static function getEngineByFile($file)
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($fileExtension) {
            case 'php':
                return Resolver\PhpFile::class;

            case 'js':
                return Resolver\JavascriptFile::class;
        }

        return null;
    }
}
