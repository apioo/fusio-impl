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

namespace Fusio\Impl\Tests\Console\Marketplace;

use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Impl\Service\Marketplace\Repository\Remote;
use Fusio\Impl\Tests\Fixture;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Client\Client;

/**
 * MarketplaceTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class MarketplaceTestCase extends ControllerDbTestCase
{
    private $installer;
    private $remote;

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    protected function getInstaller(): Installer
    {
        if ($this->installer) {
            return $this->installer;
        }

        return $this->installer = new Installer(
            Environment::getService('marketplace_repository_local'),
            $this->getRemoteRepository(),
            Environment::getService('config_service'),
            Environment::getService('config')
        );
    }

    protected function getRemoteRepository(): Remote
    {
        if ($this->remote) {
            return $this->remote;
        }

        $zipFile = $this->createAppZip();

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/plain'], $this->getMarketplaceYaml(sha1_file($zipFile))),
            new Response(200, ['Content-Length' => filesize($zipFile)], file_get_contents($zipFile)),
        ]);

        $httpClient = new Client(['handler' => HandlerStack::create($mock)]);

        return $this->remote = new Remote($httpClient, 'https://fusio.market.place');
    }

    private function getMarketplaceYaml(string $sha1Hash)
    {
        return <<<YAML
fusio:
  version: '0.7'
  description: 'The backend app is the official app to develop, configure and maintain your API.'
  screenshot: 'https://raw.githubusercontent.com/apioo/fusio/master/doc/_static/backend.png'
  website: 'https://github.com/apioo/fusio-backend'
  downloadUrl: 'https://www.fusio-project.org/files/fusio.zip'
  sha1Hash: '{$sha1Hash}'
YAML;
    }

    private function createAppZip()
    {
        $zipFile = __DIR__ . '/app.zip';
        if (is_file($zipFile)) {
            return $zipFile;
        }

        $archive = new \ZipArchive();
        $archive->open($zipFile, \ZipArchive::CREATE);
        $archive->addFromString('index.html', 'foobar');
        $archive->close();

        return $zipFile;
    }
}
