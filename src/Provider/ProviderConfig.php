<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use PSX\Framework\Config\NotFoundException;

/**
 * ProviderConfig
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderConfig
{
    const TYPE_ACTION = 'action';
    const TYPE_CONNECTION = 'connection';
    const TYPE_PAYMENT = 'payment';
    const TYPE_USER = 'user';

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $this->parse($config);
    }

    /**
     * @param string $type
     * @return array
     */
    public function getClasses($type)
    {
        return $this->config[$type] ?? [];
    }

    /**
     * @param string $type
     * @param string $name
     * @return string|null
     */
    public function getClass($type, $name)
    {
        return $this->config[$type][$name] ?? null;
    }

    /**
     * @param string $type
     * @param string $name
     * @return boolean
     */
    public function hasClass($type, $name)
    {
        return isset($this->config[$type][$name]);
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
     * @param string $file
     * @return \Fusio\Impl\Provider\ProviderConfig
     * @throws \PSX\Framework\Config\NotFoundException
     */
    public static function fromFile($file)
    {
        $config = include($file);

        if (is_array($config)) {
            return new self($config);
        } else {
            throw new NotFoundException('Config file must return an array');
        }
    }
}
