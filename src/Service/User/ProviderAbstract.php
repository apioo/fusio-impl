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

namespace Fusio\Impl\Service\User;

use PSX\Http\ClientInterface;

/**
 * ProviderAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class ProviderAbstract implements ProviderInterface
{
    /**
     * @var \PSX\Http\ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $ua = 'Fusio-Consumer (http://www.fusio-project.org/)';

    public function __construct(ClientInterface $httpClient, $secret)
    {
        $this->httpClient = $httpClient;
        $this->secret     = $secret;
    }

    abstract public function requestUser($code, $clientId, $redirectUri);
}
