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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Model\Backend\MarketplaceInstall;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Installer
{
    private Repository\Local $localRepository;
    private Repository\Remote $remoteRepository;
    private Config $config;
    private Service\Config $configService;
    private Filesystem $filesystem;

    public function __construct(Repository\Local $localRepository, Repository\Remote $remoteRepository, Service\Config $configService, Config $config)
    {
        $this->localRepository = $localRepository;
        $this->remoteRepository = $remoteRepository;
        $this->configService = $configService;
        $this->config = $config;
        $this->filesystem = new Filesystem();
    }

    public function install(MarketplaceInstall $install, UserContext $context, bool $replaceEnv = true): App
    {
        $name = $install->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        $remoteApp = $this->remoteRepository->fetchByName($name);
        $localApp = $this->localRepository->fetchByName($name);

        if (!$remoteApp instanceof App) {
            throw new StatusCode\BadRequestException('App not available');
        }

        if ($localApp instanceof App) {
            throw new StatusCode\BadRequestException('App already installed');
        }

        $this->deploy($remoteApp, $replaceEnv);

        return $remoteApp;
    }

    public function update(string $name, UserContext $context): App
    {
        $remoteApp = $this->remoteRepository->fetchByName($name);
        $localApp = $this->localRepository->fetchByName($name);

        if (!$remoteApp instanceof App) {
            throw new StatusCode\BadRequestException('App not available');
        }

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

    public function env(string $name): App
    {
        $localApp = $this->localRepository->fetchByName($name);
        if (!$localApp instanceof App) {
            throw new StatusCode\BadRequestException('App is not installed');
        }

        $appsDir = $this->config->get('fusio_apps_dir') ?: $this->config->get('psx_path_public');
        $appsDir.= '/' . $localApp->getName();

        $this->replaceVariables($appsDir);

        return $localApp;
    }

    private function deploy(App $remoteApp, bool $replaceEnv = true)
    {
        $zipFile = $this->downloadZip($remoteApp);

        $appDir = $this->config->get('psx_path_cache') . '/app-' . $remoteApp->getName();
        $appDir = $this->unzipFile($zipFile, $appDir);

        $this->writeMetaFile($appDir, $remoteApp);

        if ($replaceEnv) {
            $this->replaceVariables($appDir);
        }

        $this->moveToPublic($appDir, $remoteApp);
    }

    private function downloadZip(App $app): string
    {
        $appFile = $this->config->get('psx_path_cache') . '/app-' . $app->getName() . '_' . uniqid() . '.zip';

        $this->remoteRepository->downloadZip($app, $appFile);

        // check hash
        if (sha1_file($appFile) !== $app->getSha1Hash()) {
            throw new StatusCode\InternalServerErrorException('Invalid hash of downloaded app');
        }

        return $appFile;
    }

    private function unzipFile(string $zipFile, string $appDir): string
    {
        $zip = new \ZipArchive();
        $handle = $zip->open($zipFile);

        if (!$handle) {
            throw new StatusCode\InternalServerErrorException('Could not open zip file');
        }

        $zip->extractTo($appDir);
        $zip->close();

        // check whether there is only a single folder inside the zip
        $files = scandir($appDir);
        if (count($files) === 3 && is_dir($appDir . '/' . $files[2])) {
            return $appDir . '/' . $files[2];
        } else {
            return $appDir;
        }
    }

    private function writeMetaFile(string $appDir, App $app): void
    {
        if (!file_put_contents($appDir . '/app.yaml', Yaml::dump($app->toArray()))) {
            throw new StatusCode\InternalServerErrorException('Could not write app meta file');
        }
    }

    private function moveToPublic(string $appDir, App $app): void
    {
        $appsDir = $this->config->get('fusio_apps_dir') ?: $this->config->get('psx_path_public');

        $this->filesystem->rename($appDir, $appsDir . '/' . $app->getName());
    }

    private function moveToTrash(App $app): void
    {
        $appsDir = $this->config->get('fusio_apps_dir') ?: $this->config->get('psx_path_public');
        $appDir = $appsDir . '/' . $app->getName();

        $this->filesystem->rename($appDir, $this->config->get('psx_path_cache') . '/' . $app->getName() . '_' . $app->getVersion() . '_' . uniqid());
    }

    private function replaceVariables(string $appDir): void
    {
        $apiUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $url = $this->config->get('fusio_apps_url');
        $basePath = parse_url($url, PHP_URL_PATH);

        $env = array_merge($_ENV, [
            'API_URL' => $apiUrl,
            'URL' => $url,
            'BASE_PATH' => $basePath,
        ]);

        // set values from config
        $configValues = [
            'PROVIDER_FACEBOOK_KEY' => 'provider_facebook_key',
            'PROVIDER_GOOGLE_KEY' => 'provider_google_key',
            'PROVIDER_GITHUB_KEY' => 'provider_github_key',
            'RECAPTCHA_KEY' => 'recaptcha_key',
        ];

        foreach ($configValues as $key => $name) {
            $value = $this->configService->getValue($name);
            if (!empty($value)) {
                $env[$key] = $value;
            } elseif (!isset($env[$key])) {
                $env[$key] = '';
            }
        }

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
