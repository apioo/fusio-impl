<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Provider;

use PSX\Framework\Config\Config;

/**
 * ProviderConfig
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderConfig extends Config
{
    const TYPE_ACTION = 'action';
    const TYPE_CONNECTION = 'connection';
    const TYPE_PAYMENT = 'payment';
    const TYPE_USER = 'user';
    const TYPE_ROUTES = 'routes';
    const TYPE_PUSH = 'push';

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($this->parse($config));
    }

    /**
     * @param string $type
     * @return array
     */
    public function getClasses(string $type)
    {
        return $this->get($type);
    }

    /**
     * @param string $type
     * @param string $name
     * @return string|null
     */
    public function getClass(string $type, string $name)
    {
        return $this->get($type)[$name] ?? null;
    }

    /**
     * @param array $config
     * @return array
     */
    private function parse(array $config)
    {
        $result = [];
        foreach ($config as $name => $classes) {
            $result[$name] = [];
            foreach ($classes as $class) {
                try {
                    $reflection = new \ReflectionClass($class);
                    $shortName  = strtolower($reflection->getShortName());

                    $result[$name][$shortName] = $class;
                } catch (\ReflectionException $e) {
                    // class does not exist
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ACTION,
            self::TYPE_CONNECTION,
            self::TYPE_PAYMENT,
            self::TYPE_USER,
            self::TYPE_ROUTES,
            self::TYPE_PUSH,
        ];
    }
}
