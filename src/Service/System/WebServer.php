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

namespace Fusio\Impl\Service\System;

use Fusio\Impl\Service\System\Import\Result;
use Fusio\Impl\Service\System\WebServer\Configuration;
use Fusio\Impl\Service\System\WebServer\Generator\Apache2;
use Fusio\Impl\Service\System\WebServer\Generator\Nginx;
use Fusio\Impl\Service\System\WebServer\GeneratorInterface;
use Fusio\Impl\Service\System\WebServer\VirtualHost;
use PSX\Framework\Config\Config;

/**
 * WebServer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class WebServer
{
    const APACHE2 = 'apache2';
    const NGINX   = 'nginx';

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $server
     * @return \Fusio\Impl\Service\System\Import\Result
     */
    public function generate(array $server)
    {
        $result = new Result();
        $config = new Configuration();

        if (isset($server['api']) && is_array($server['api'])) {
            $host = VirtualHost::fromArray($server['api'], VirtualHost::HANDLER_API);

            $this->assertHost($host, $result, false);

            $config->addVirtualHost($host);
        }

        if (isset($server['apps']) && is_array($server['apps'])) {
            foreach ($server['apps'] as $app) {
                if (is_array($app)) {
                    $host = VirtualHost::fromArray($app, VirtualHost::HANDLER_APP);

                    $this->assertHost($host, $result, true);

                    $config->addVirtualHost($host);
                }
            }
        }

        $generator = $this->newGenerator();
        if ($generator instanceof GeneratorInterface) {
            $file  = $this->config->get('fusio_server_conf');
            $bytes = $generator->generate($config, $file);

            if ($bytes === false) {
                $result->add(Deploy::TYPE_SERVER, Result::ACTION_FAILED, 'Could not write web server config file  ' . $file);
            } elseif ($bytes > 0) {
                $result->add(Deploy::TYPE_SERVER, Result::ACTION_GENERATED, 'Generated web server config file  ' . $file);
            }
        }

        return $result;
    }

    /**
     * @return \Fusio\Impl\Service\System\WebServer\GeneratorInterface|null
     */
    private function newGenerator()
    {
        $engine = $this->config->get('fusio_server_type');

        if ($engine === self::APACHE2) {
            return new Apache2();
        } elseif ($engine === self::NGINX) {
            return new Nginx();
        }

        return null;
    }

    /**
     * @param \Fusio\Impl\Service\System\WebServer\VirtualHost $host
     * @param \Fusio\Impl\Service\System\Import\Result $result
     * @param boolean $replaceEnv
     */
    private function assertHost(VirtualHost $host, Result $result, $replaceEnv)
    {
        $root  = $host->getDocumentRoot();
        $index = $host->getIndex();

        if (!is_dir($root)) {
            throw new \RuntimeException('Virtual host root directory ' . $root . ' does not exist');
        }

        if (!empty($index)) {
            $indexFile = $root . '/' . $index;

            if (!is_file($indexFile)) {
                throw new \RuntimeException('Virtual host index file ' . $indexFile . ' does not exist');
            }

            if ($replaceEnv) {
                $this->replaceFile($indexFile, $result);
            }
        }
    }

    /**
     * @param string $index
     * @param \Fusio\Impl\Service\System\Import\Result $result
     */
    private function replaceFile($index, Result $result)
    {
        $content = file_get_contents($index);
        $content = $this->replaceEnvs($content, $count);

        if ($count > 0) {
            $bytes = file_put_contents($index, $content);

            if ($bytes) {
                $result->add(Deploy::TYPE_SERVER, Result::ACTION_REPLACED, 'Environment variables at ' . $index);
            }
        }
    }

    /**
     * @param string $content
     * @param integer $replaced
     * @return string
     */
    private function replaceEnvs($content, &$replaced)
    {
        $envs = [
            'FUSIO_URL' => $this->config->get('psx_url'),
        ];

        $envs = array_merge($envs, $_SERVER);

        foreach ($envs as $key => $value) {
            if (is_scalar($value)) {
                $content = str_replace('${' . $key . '}', $value, $content, $count);
                $replaced+= $count;
            }
        }

        return $content;
    }
}
