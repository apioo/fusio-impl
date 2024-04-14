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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Dto\Marketplace\App;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\MarketplaceInstall;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Installer
{
    private Repository\Local $localRepository;
    private Repository\Remote $remoteRepository;
    private Service\Config $configService;
    private Service\System\FrameworkConfig $frameworkConfig;
    private Filesystem $filesystem;
    private Table\App $appTable;

    public function __construct(Repository\Local $localRepository, Repository\Remote $remoteRepository, Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig, Table\App $appTable)
    {
        $this->localRepository = $localRepository;
        $this->remoteRepository = $remoteRepository;
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
        $this->filesystem = new Filesystem();
        $this->appTable = $appTable;
    }

    public function install(MarketplaceInstall $install, bool $replaceEnv, UserContext $context): App
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

        $this->deploy($remoteApp, $replaceEnv, $context);

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

    public function env(string $name, UserContext $context): App
    {
        $localApp = $this->localRepository->fetchByName($name);
        if (!$localApp instanceof App) {
            throw new StatusCode\BadRequestException('App is not installed');
        }

        $appsDir = $this->frameworkConfig->getAppsDir();
        $appsDir.= '/' . $localApp->getName();

        $this->replaceVariables($appsDir, $localApp->getName(), $context);

        return $localApp;
    }

    private function deploy(App $remoteApp, bool $replaceEnv, UserContext $context)
    {
        $zipFile = $this->downloadZip($remoteApp);

        $appDir = $this->frameworkConfig->getPathCache('app-' . $remoteApp->getName());
        $appDir = $this->unzipFile($zipFile, $appDir);

        $this->writeMetaFile($appDir, $remoteApp);

        if ($replaceEnv) {
            $this->replaceVariables($appDir, $remoteApp->getName(), $context);
        }

        $this->moveToPublic($appDir, $remoteApp);
    }

    private function downloadZip(App $app): string
    {
        $appFile = $this->frameworkConfig->getPathCache('app-' . $app->getName() . '_' . uniqid() . '.zip');

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
        $appsDir = $this->frameworkConfig->getAppsDir();

        $this->filesystem->rename($appDir, $appsDir . '/' . $app->getName());
    }

    private function moveToTrash(App $app): void
    {
        $appsDir = $this->frameworkConfig->getAppsDir();
        $appDir = $appsDir . '/' . $app->getName();

        $this->filesystem->rename($appDir, $this->frameworkConfig->getPathCache($app->getName() . '_' . $app->getVersion() . '_' . uniqid()));
    }

    private function replaceVariables(string $appDir, string $appName, UserContext $context): void
    {
        $apiUrl = $this->frameworkConfig->getDispatchUrl();
        $url = $this->frameworkConfig->getAppsUrl();
        $basePath = parse_url($url, PHP_URL_PATH);

        $app = $this->appTable->findOneByTenantAndName($context->getTenantId(), $appName);
        if ($app instanceof Table\Generated\AppRow) {
            $appKey = $app->getAppKey();
        } else {
            $appKey = '';
        }

        $env = array_merge($_ENV, [
            'API_URL' => $apiUrl,
            'URL' => $url,
            'BASE_PATH' => $basePath,
            'APP_KEY' => $appKey,
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
