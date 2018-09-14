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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use PSX\Http\Client\ClientInterface;

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
     * @var \PSX\Http\Client\ClientInterface
     */
    protected $httpClient;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var array
     */
    protected $providers;

    public function __construct(ClientInterface $httpClient, Service\Config $configService)
    {
        $this->httpClient    = $httpClient;
        $this->configService = $configService;
        $this->providers     = [];
    }

    /**
     * Registers a user provider class which can be used to authenticate
     * 
     * @param string $name
     * @param string $class
     */
    public function register($name, $class)
    {
        $this->providers[$name] = $class;
    }

    /**
     * @param string $provider
     * @return \Fusio\Impl\Service\User\ProviderInterface|null
     */
    public function factory($provider)
    {
        $provider = strtolower($provider);

        if (isset($this->providers[$provider])) {
            $secret = $this->configService->getValue('provider_' . $provider . '_secret');
            $class  = $this->providers[$provider];

            if (!empty($secret)) {
                return new $class($this->httpClient, $secret);
            }
        }

        return null;
    }
}
