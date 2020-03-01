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

namespace Fusio\Impl\Service\System\Push;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Provider
{
    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $prepareUrl;

    /**
     * @var string
     */
    protected $pushUrl;

    /**
     * @var string
     */
    protected $statusUrl;

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getPrepareUrl()
    {
        return $this->prepareUrl;
    }

    /**
     * @param string $prepareUrl
     */
    public function setPrepareUrl($prepareUrl)
    {
        $this->prepareUrl = $prepareUrl;
    }
    
    /**
     * @return string
     */
    public function getPushUrl()
    {
        return $this->pushUrl;
    }

    /**
     * @param string $pushUrl
     */
    public function setPushUrl($pushUrl)
    {
        $this->pushUrl = $pushUrl;
    }

    /**
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->statusUrl;
    }

    /**
     * @param string $statusUrl
     */
    public function setStatusUrl($statusUrl)
    {
        $this->statusUrl = $statusUrl;
    }

    /**
     * @param array $data
     * @return \Fusio\Impl\Service\System\Push\Provider
     */
    public static function fromArray(array $data)
    {
        $provider = new self();

        if (isset($data['hostname']) && is_string($data['hostname'])) {
            $provider->setHostname($data['hostname']);
        }

        if (isset($data['prepareUrl']) && is_string($data['prepareUrl'])) {
            $provider->setPrepareUrl($data['prepareUrl']);
        }

        if (isset($data['pushUrl']) && is_string($data['pushUrl'])) {
            $provider->setPushUrl($data['pushUrl']);
        }

        if (isset($data['statusUrl']) && is_string($data['statusUrl'])) {
            $provider->setStatusUrl($data['statusUrl']);
        }

        return $provider;
    }
}
