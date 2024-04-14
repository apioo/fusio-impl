<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Command\Marketplace;

use Fusio\Impl\Service\Config;
use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Impl\Service\Marketplace\Repository\Local;
use Fusio\Impl\Service\Marketplace\Repository\Remote;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Client\Client;
use PSX\Sql\TableManagerInterface;

/**
 * MarketplaceTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MarketplaceTestCase extends ControllerDbTestCase
{
    private ?Installer $installer = null;
    private ?Remote $remote = null;

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
            Environment::getService(Local::class),
            $this->getRemoteRepository(),
            Environment::getService(Config::class),
            Environment::getService(FrameworkConfig::class),
            Environment::getService(TableManagerInterface::class)->getTable(Table\App::class),
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

        return $this->remote = new Remote($httpClient, Environment::getService(FrameworkConfig::class));
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
