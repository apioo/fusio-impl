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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use PSX\Framework\Config\Config;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\Options;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\Yaml\Yaml;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Installer
{
    /**
     * @var \Fusio\Impl\Service\Marketplace\Repository\Local
     */
    private $localRepository;

    /**
     * @var \Fusio\Impl\Service\Marketplace\Repository\Remote
     */
    private $remoteRepository;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Fusio\Impl\Service\Marketplace\Repository\Local $localRepository
     * @param \Fusio\Impl\Service\Marketplace\Repository\Remote $remoteRepository
     * @param \PSX\Http\Client\ClientInterface $httpClient
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Repository\Local $localRepository, Repository\Remote $remoteRepository, ClientInterface $httpClient, Config $config)
    {
        $this->localRepository = $localRepository;
        $this->remoteRepository = $remoteRepository;
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    public function install(string $name, UserContext $context): App
    {
        $remoteApp = $this->remoteRepository->fetchByName($name);
        $localApp = $this->localRepository->fetchByName($name);

        if ($localApp instanceof App) {
            throw new StatusCode\BadRequestException('App already installed');
        }

        $this->deploy($remoteApp);

        return $remoteApp;
    }

    public function update(string $name, UserContext $context): App
    {
        $remoteApp = $this->remoteRepository->fetchByName($name);
        $localApp = $this->localRepository->fetchByName($name);

        if (!$localApp instanceof App) {
            throw new StatusCode\BadRequestException('App is not installed');
        }

        if (version_compare($remoteApp->getVersion(), $localApp->getVersion()) === 0) {
            throw new StatusCode\BadRequestException('App is already up-to-date');
        }

        if (version_compare($remoteApp->getVersion(), $localApp->getVersion()) === -1) {
            throw new StatusCode\BadRequestException('Local version of the app has a higher version');
        }

        $this->moveToTrash($localApp);

        $this->deploy($remoteApp);

        return $remoteApp;
    }

    public function remove(string $name, UserContext $context): App
    {
        $localApp = $this->localRepository->fetchByName($name);

        if (!$localApp instanceof App) {
            throw new StatusCode\BadRequestException('App is not installed');
        }

        $this->moveToTrash($localApp);

        return $localApp;
    }

    private function deploy(App $remoteApp)
    {
        $zipFile = $this->downloadZip($remoteApp);

        $appDir = $this->config->get('psx_path_cache') . '/app-' . $remoteApp->getName();
        $this->unzipFile($zipFile, $appDir);

        $this->writeMetaFile($appDir, $remoteApp);
        $this->replaceVariables($appDir);

        $this->moveToPublic($appDir, $remoteApp);
    }
    
    private function downloadZip(App $app): string
    {
        $options = new Options();
        $options->setVerify(false);

        $response = $this->httpClient->request(new GetRequest($app->getDownloadUrl()), $options);

        $appFile = $this->config->get('psx_path_cache') . '/app-' . $app->getName() . '_' . uniqid() . '.zip';
        file_put_contents($appFile, $response->getBody()->getContents());

        // check hash
        if (sha1_file($appFile) != $app->getSha1Hash()) {
            throw new StatusCode\InternalServerErrorException('Invalid hash of downloaded app');
        }

        return $appFile;
    }

    private function unzipFile(string $zipFile, string $appDir): void
    {
        $zip = new \ZipArchive();
        $handle = $zip->open($zipFile);

        if (!$handle) {
            throw new StatusCode\InternalServerErrorException('Could not open zip file');
        }

        $zip->extractTo($appDir);
    }

    private function writeMetaFile(string $appDir, App $app): void
    {
        if (!file_put_contents($appDir . '/app.yaml', Yaml::dump($app->toArray()))) {
            throw new StatusCode\InternalServerErrorException('Could not write app meta file');
        }
    }

    private function moveToPublic(string $appDir, App $app): void
    {
        if (!rename($appDir, $this->config->get('psx_path_public') . '/' . $app->getName())) {
            throw new StatusCode\InternalServerErrorException('Could not move app to public');
        }
    }

    private function moveToTrash(App $app): void
    {
        $appDir = $this->config->get('psx_path_public') . '/' . $app->getName();

        if (!rename($appDir, $this->config->get('psx_path_cache') . '/' . $app->getName() . '_' . $app->getVersion() . '_' . uniqid())) {
            throw new StatusCode\InternalServerErrorException('Could not move existing app to trash');
        }
    }

    private function replaceVariables(string $appDir)
    {
        $apiUrl = $this->config->get('psx_url') . $this->config->get('psx_dispatch');
        $url = $this->config->get('psx_url');
        $basePath = parse_url($url, PHP_URL_PATH);

        $env = [
            'API_URL' => $apiUrl,
            'URL' => $url,
            'BASE_PATH' => $basePath,
        ];

        $file = $appDir . '/index.html';
        if (is_file($file)) {
            $content = file_get_contents($file);

            foreach ($env as $key => $value) {
                $content = str_replace('${' . $key . '}', $value, $content);
            }

            file_put_contents($file, $content);
        }
    }
}
