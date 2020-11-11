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

namespace Fusio\Impl\Tests\Service\System;

use Fusio\Impl\Service\System\WebServer;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Config\Config;

/**
 * WebServerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class WebServerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        file_put_contents(__DIR__ . '/apps/test-app/index.html', '<b>Url: ${FUSIO_URL}</b>');
    }

    /**
     * @dataProvider serverTypeProvider
     */
    public function testGenerate($serverType)
    {
        $server = $this->newWebServer($serverType);
        $result = $server->generate([
            'api' => [
                'host' => 'api.apioo.de',
                'root' => __DIR__ . '/apps/test-app',
                'index' => 'index.html',
                'ssl_cert' => '/tmp/domain.crt',
                'ssl_cert_key' => '/tmp/private.key',
                'error_log' => '/tmp/error.log',
                'access_log' => '/tmp/access.log',
            ],
            'apps' => [
                'host' => 'apps.apioo.de',
                'alias' => ['myapp.com', 'foo.com'],
                'root' => __DIR__ . '/apps/test-app',
                'index' => 'index.html',
            ],
        ]);

        $actual = file_get_contents(__DIR__ . '/Resource/' . $serverType . '.conf');
        $expect = file_get_contents(__DIR__ . '/Resource/' . $serverType . '_expect.conf');

        $actual = str_replace(__DIR__, '', $actual);
        $actual = preg_replace('/\d{4}-\d{2}-\d{2}/', '0000-00-00', $actual);

        $this->assertEquals($expect, $actual, $actual);

        $actual = $result->getLogs();
        $expect = [
            '[GENERATED] server Generated web server config file  /Resource/' . $serverType . '.conf',
        ];

        foreach ($expect as $i => $message) {
            $this->assertEquals($message, str_replace(__DIR__, '', $actual[$i]));
        }
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
            'psx_url'           => 'http://foo.bar',
            'fusio_server_type' => $serverType,
            'fusio_server_conf' => __DIR__ . '/Resource/' . $serverType . '.conf',
        ]);
        $webServer = new WebServer($config);

        return $webServer;
    }
}