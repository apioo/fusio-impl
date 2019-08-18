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

use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
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
     * @var string
     */
    private $publicPath;

    /**
     * @param \Fusio\Impl\Service\Marketplace\Repository\Local $localRepository
     * @param \Fusio\Impl\Service\Marketplace\Repository\Remote $remoteRepository
     * @param \PSX\Http\Client\ClientInterface $httpClient
     * @param string $publicPath
     */
    public function __construct(Repository\Local $localRepository, Repository\Remote $remoteRepository, ClientInterface $httpClient, string $publicPath)
    {
        $this->localRepository = $localRepository;
        $this->remoteRepository = $remoteRepository;
        $this->httpClient = $httpClient;
        $this->publicPath = $publicPath;
    }

    /**
     * Installs or updates an new app
     * 
     * @param string $name
     */
    public function install(string $name)
    {
        $remoteApp = $this->remoteRepository->fetchByName($name);
        $localApp = $this->localRepository->fetchByName($name);
        $localDir = null;

        if ($localApp instanceof App) {
            if (version_compare($remoteApp->getVersion(), $localApp->getVersion()) === 0) {
                // local and remote have the same version
                return;
            }

            if (version_compare($remoteApp->getVersion(), $localApp->getVersion()) === -1) {
                // this means we have locally a newer version then remote, maybe
                // if a user has manually changed the version so do nothing
                return;
            }

            // if we are here the remote version is newer so we need to update
            $localDir = $this->publicPath . '/' . $localApp->getName();
        }

        $zipFile = $this->downloadZip($remoteApp);

        $appDir = PSX_PATH_CACHE . '/app-' . $remoteApp->getName();
        $this->unzipFile($zipFile, $appDir);

        $this->writeMetaFile($appDir, $remoteApp);

        if ($localDir !== null) {
            $this->moveToTrash($localDir, $localApp);
        }

        $this->moveToPublic($appDir, $remoteApp);
    }

    private function downloadZip(App $app): string
    {
        $response = $this->httpClient->request(new GetRequest($app->getDownloadUrl()));

        $appFile = PSX_PATH_CACHE . '/app-' . $app->getName() . '.zip';
        file_put_contents($appFile, $response->getBody()->getContents());

        // check hash
        if (sha1_file($appFile) != $app->getSha1Hash()) {
            throw new \RuntimeException('Invalid hash of downloaded app');
        }

        return $appFile;
    }

    private function unzipFile(string $zipFile, string $appDir): string
    {
        $zip = new \ZipArchive();
        $handle = $zip->open($zipFile);

        if (!$handle) {
            throw new \RuntimeException('Could not open zip file');
        }

        $zip->extractTo($appDir);
    }

    private function writeMetaFile(string $appDir, App $app): string
    {
        file_put_contents($appDir . '/app.yaml', Yaml::dump($app->toArray()));
    }

    private function moveToPublic(string $appDir, App $app): void
    {
        if (!rename($appDir, $this->publicPath . '/' . $app->getName())) {
            throw new \RuntimeException('Could not move app to public');
        }
    }

    private function moveToTrash(string $appDir, App $app): void
    {
        if (!rename($appDir, PSX_PATH_CACHE . '/' . $app->getName() . '_' . $app->getVersion())) {
            throw new \RuntimeException('Could not move existing app to trash');
        }
    }
}
