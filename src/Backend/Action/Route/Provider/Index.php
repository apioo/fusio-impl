<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Route\Provider;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Provider\ProviderLoader;
use PSX\Dependency\AutowireResolverInterface;

/**
 * Index
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Index extends ActionAbstract
{
    private ProviderLoader $loader;
    private AutowireResolverInterface $resolver;

    public function __construct(ProviderLoader $loader, AutowireResolverInterface $resolver)
    {
        $this->loader = $loader;
        $this->resolver = $resolver;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $classes = $this->loader->getConfig()->getClasses(ProviderConfig::TYPE_ROUTES);
        $result  = [];

        foreach ($classes as $name => $class) {
            $provider = $this->resolver->getObject($class);
            if ($provider instanceof ProviderInterface) {
                $result[] = [
                    'name' => $provider->getName(),
                    'class' => $name,
                ];
            }
        }

        return [
            'providers' => $result
        ];
    }
}
