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

namespace Fusio\Impl\Service\System\WebServer;

/**
 * VirtualHost
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class VirtualHost
{
    const HANDLER_APP = 'app';
    const HANDLER_API = 'api';

    /**
     * @var string
     */
    protected $handler;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var array
     */
    protected $alias;

    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * @var string
     */
    protected $index;

    /**
     * @var integer
     */
    protected $sslPort;

    /**
     * @var string
     */
    protected $sslCertificate;

    /**
     * @var string
     */
    protected $sslCertificateKey;

    /**
     * @var boolean
     */
    protected $sslForce;

    /**
     * @var string
     */
    protected $errorLog;

    /**
     * @var string
     */
    protected $accessLog;

    /**
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param string $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param integer $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param string $serverName
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
    }

    /**
     * @return array
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param array $alias
     */
    public function setAlias(array $alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * @param string $documentRoot
     */
    public function setDocumentRoot($documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getSslPort()
    {
        return $this->sslPort;
    }

    /**
     * @param int $sslPort
     */
    public function setSslPort($sslPort)
    {
        $this->sslPort = $sslPort;
    }
    
    /**
     * @return string
     */
    public function getSslCertificate()
    {
        return $this->sslCertificate;
    }

    /**
     * @param string $sslCertificate
     */
    public function setSslCertificate($sslCertificate)
    {
        $this->sslCertificate = $sslCertificate;
    }

    /**
     * @return string
     */
    public function getSslCertificateKey()
    {
        return $this->sslCertificateKey;
    }

    /**
     * @param string $sslCertificateKey
     */
    public function setSslCertificateKey($sslCertificateKey)
    {
        $this->sslCertificateKey = $sslCertificateKey;
    }

    /**
     * @return bool
     */
    public function getSslForce()
    {
        return $this->sslForce;
    }

    /**
     * @param bool $sslForce
     */
    public function setSslForce($sslForce)
    {
        $this->sslForce = $sslForce;
    }

    /**
     * @return string
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * @param string $errorLog
     */
    public function setErrorLog($errorLog)
    {
        $this->errorLog = $errorLog;
    }

    /**
     * @return string
     */
    public function getAccessLog()
    {
        return $this->accessLog;
    }

    /**
     * @param string $accessLog
     */
    public function setAccessLog($accessLog)
    {
        $this->accessLog = $accessLog;
    }

    /**
     * @param array $data
     * @param string $handler
     * @return \Fusio\Impl\Service\System\WebServer\VirtualHost
     */
    public static function fromArray(array $data, $handler)
    {
        $host = new self();
        $host->setHandler($handler);

        if (isset($data['port'])) {
            $host->setPort($data['port']);
        } else {
            $host->setPort(80);
        }

        if (isset($data['host'])) {
            $host->setServerName($data['host']);
        }

        if (isset($data['alias']) && is_array($data['alias'])) {
            $host->setAlias($data['alias']);
        }

        if (isset($data['root'])) {
            $host->setDocumentRoot($data['root']);
        }

        if (isset($data['index'])) {
            $host->setIndex($data['index']);
        }

        if (isset($data['ssl_port'])) {
            $host->setSslPort($data['ssl_port']);
        } else {
            $host->setSslPort(443);
        }

        if (isset($data['ssl_cert'])) {
            $host->setSslCertificate($data['ssl_cert']);
        }

        if (isset($data['ssl_cert_key'])) {
            $host->setSslCertificateKey($data['ssl_cert_key']);
        }

        if (isset($data['ssl_force'])) {
            $host->setSslForce($data['ssl_force']);
        }

        if (isset($data['error_log'])) {
            $host->setErrorLog($data['error_log']);
        }

        if (isset($data['access_log'])) {
            $host->setAccessLog($data['access_log']);
        }

        return $host;
    }
}
