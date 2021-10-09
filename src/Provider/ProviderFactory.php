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

use PSX\Dependency\AutowireResolverInterface;

/**
 * ProviderFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderFactory
{
    /**
     * @var \Fusio\Impl\Provider\ProviderLoader
     */
    protected $loader;

    /**
     * @var \PSX\Dependency\AutowireResolverInterface
     */
    protected $resolver;

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
     * @param \PSX\Dependency\AutowireResolverInterface $resolver
     * @param string $type
     * @param string $instanceOf
     */
    public function __construct(ProviderLoader $loader, AutowireResolverInterface $resolver, string $type, string $instanceOf)
    {
        $this->loader     = $loader;
        $this->resolver   = $resolver;
        $this->type       = $type;
        $this->instanceOf = $instanceOf;
    }

    /**
     * @param string $provider
     * @return object|null
     */
    public function factory(string $provider)
    {
        $provider = strtolower($provider);
        $class    = $this->loader->getConfig()->getClass($this->type, $provider);

        if ($class !== null) {
            return $this->newInstance($class);
        }

        return null;
    }

    /**
     * @param string $class
     * @return object
     */
    protected function newInstance(string $class)
    {
        $instance = $this->resolver->getObject($class);

        if (!$instance instanceof $this->instanceOf) {
            throw new \RuntimeException('Provided class must be an instance of ' . $this->instanceOf);
        }

        return $instance;
    }
}
