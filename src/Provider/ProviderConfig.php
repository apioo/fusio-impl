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
    public const TYPE_ACTION = 'action';
    public const TYPE_CONNECTION = 'connection';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_USER = 'user';
    public const TYPE_ROUTES = 'routes';

    public function __construct(array $config)
    {
        parent::__construct($this->parse($config));
    }

    public function getClasses(string $type): array
    {
        return $this->get($type);
    }

    public function getClass(string $type, string $name): ?string
    {
        return $this->get($type)[$name] ?? null;
    }

    private function parse(array $config): array
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

    public static function getTypes(): array
    {
        return [
            self::TYPE_ACTION,
            self::TYPE_CONNECTION,
            self::TYPE_PAYMENT,
            self::TYPE_USER,
            self::TYPE_ROUTES,
        ];
    }
}
