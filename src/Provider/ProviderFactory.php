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

namespace Fusio\Impl\Provider;

use Fusio\Engine\Factory\ContainerAwareInterface;
use Psr\Container\ContainerInterface;

/**
 * ProviderFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderFactory
{
    /**
     * @var \Fusio\Impl\Provider\ProviderLoader
     */
    protected $loader;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $instanceOf;

    /**
     * @param \Fusio\Impl\Provider\ProviderLoader $loader
     * @param \Psr\Container\ContainerInterface $container
     * @param string $type
     * @param string $instanceOf
     */
    public function __construct(ProviderLoader $loader, ContainerInterface $container, $type, $instanceOf)
    {
        $this->loader     = $loader;
        $this->container  = $container;
        $this->type       = $type;
        $this->instanceOf = $instanceOf;
    }

    /**
     * @param string $provider
     * @return object|null
     */
    public function factory($provider)
    {
        $provider = strtolower($provider);
        $class    = $this->loader->getConfig()->getClass($this->type, $provider);

        if ($class !== null) {
            return $this->newInstance($class, $provider);
        }

        return null;
    }

    /**
     * @param string $class
     * @param string $provider
     * @return object
     */
    protected function newInstance($class, $provider)
    {
        $instance = new $class($provider);

        if (!$instance instanceof $this->instanceOf) {
            throw new \RuntimeException('Provided class must be an instance of ' . $this->instanceOf);
        }

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}
