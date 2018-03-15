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

namespace Fusio\Impl\Tests\Service\System;

use Fusio\Impl\Service\System\Deploy\EnvProperties;
use Fusio\Impl\Service\System\WebServer;
use PSX\Framework\Config\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * WebServerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class WebServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider serverTypeProvider
     */
    public function testGenerate($serverType)
    {
        $webServer = $this->newWebServer($serverType);
        $webServer->generate([
            'api' => [
                'host' => 'api.apioo.de',
                'root' => '/var/www/html/fusio',
                'index' => 'index.php',
                'ssl_cert' => '/tmp/domain.crt',
                'ssl_cert_key' => '/tmp/private.key',
                'error_log' => '/tmp/error.log',
                'access_log' => '/tmp/access.log',
            ],
            'apps' => [
                [
                    'host' => 'fusio.apioo.de',
                    'root' => '/var/www/html/fusio/fusio',
                    'index' => 'index.htm',
                ],
                [
                    'host' => 'documentation.apioo.de',
                    'root' => '/var/www/html/fusio/documentation',
                    'index' => 'index.html',
                ],
                [
                    'host' => 'developer.apioo.de',
                    'root' => '/var/www/html/fusio/developer',
                    'index' => 'index.html',
                ],
            ],
        ]);

        $actual = file_get_contents(__DIR__ . '/Resource/' . $serverType . '.conf');
        $expect = file_get_contents(__DIR__ . '/Resource/' . $serverType . '_expect.conf');

        $actual = preg_replace('/\d{4}-\d{2}-\d{2}/', '0000-00-00', $actual);

        $this->assertEquals($expect, $actual, $actual);
    }

    public function serverTypeProvider()
    {
        return [
            [WebServer::APACHE2],
            [WebServer::NGINX],
        ];
    }

    private function newWebServer($serverType)
    {
        $config    = new Config([
            'fusio_server_type' => $serverType,
            'fusio_server_conf' => __DIR__ . '/Resource/' . $serverType . '.conf',
        ]);
        $webServer = new WebServer($config);

        return $webServer;
    }
}