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

namespace Fusio\Impl\Service\Marketplace\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Exception\MarketplaceException;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace\InstallerInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Marketplace\MarketplaceApp;
use Fusio\Marketplace\MarketplaceObject;
use Fusio\Model\Backend\AppCreate;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\Options;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Installer implements InstallerInterface
{
    private Service\App $appService;
    private Service\Config $configService;
    private FrameworkConfig $frameworkConfig;
    private Table\App $appTable;
    private Table\User $userTable;
    private ClientInterface $httpClient;
    private Filesystem $filesystem;
    private bool $replaceEnv = true;

    public function __construct(Service\App $appService, Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig, Table\App $appTable, Table\User $userTable, ClientInterface $httpClient)
    {
        $this->appService = $appService;
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
        $this->appTable = $appTable;
        $this->userTable = $userTable;
        $this->httpClient = $httpClient;
        $this->filesystem = new Filesystem();
    }

    public function install(MarketplaceObject $object, UserContext $context): void
    {
        if (!$object instanceof MarketplaceApp) {
            throw new MarketplaceException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $zipFile = $this->downloadZip($object);

        $appDir = $this->frameworkConfig->getPathCache('app-' . $object->getName());
        $appDir = $this->unzipFile($zipFile, $appDir);

        $this->writeMetaFile($appDir, $object);

        if ($this->replaceEnv) {
            $this->replaceVariables($appDir, $object, $context);
        }

        $this->moveToPublic($appDir, $object);
    }

    public function upgrade(MarketplaceObject $object, UserContext $context): void
    {
        if (!$object instanceof MarketplaceApp) {
            throw new MarketplaceException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $this->moveToTrash($object);

        $this->install($object, $context);
    }

    public function isInstalled(MarketplaceObject $object, UserContext $context): bool
    {
        if (!$object instanceof MarketplaceApp) {
            throw new MarketplaceException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $appsDir = $this->frameworkConfig->getAppsDir();
        $appDir = $appsDir . '/' . $object->getName();

        return is_dir($appDir);
    }

    public function env(MarketplaceApp $object, UserContext $context): MarketplaceApp
    {
        $appsDir = $this->frameworkConfig->getAppsDir();
        $appDir = $appsDir . '/' . $object->getName();

        if (!is_dir($appDir)) {
            throw new MarketplaceException('Provided app does not exist');
        }

        $this->replaceVariables($appDir, $object, $context);

        return $object;
    }

    public function setReplaceEnv(bool $replaceEnv): void
    {
        $this->replaceEnv = $replaceEnv;
    }

    private function downloadZip(MarketplaceApp $app): string
    {
        $appFile = $this->frameworkConfig->getPathCache('app-' . $app->getName() . '_' . uniqid() . '.zip');

        $downloadUrl = $app->getDownloadUrl();
        if (empty($downloadUrl)) {
            throw new MarketplaceException('Download url is not available for this app');
        }

        // increase timeout to handle download
        set_time_limit(300);

        $options = new Options();
        $options->setVerify(false);
        $options->setAllowRedirects(true);

        $response = $this->httpClient->request(new GetRequest($downloadUrl), $options);

        file_put_contents($appFile, $response->getBody()->getContents());

        // check hash
        if (sha1_file($appFile) !== $app->getHash()) {
            throw new MarketplaceException('Invalid hash of downloaded app');
        }

        return $appFile;
    }

    private function unzipFile(string $zipFile, string $appDir): string
    {
        $zip = new \ZipArchive();
        $handle = $zip->open($zipFile);

        if (!$handle) {
            throw new MarketplaceException('Could not open zip file');
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

    private function writeMetaFile(string $appDir, MarketplaceApp $app): void
    {
        if (!file_put_contents($appDir . '/app.json', \json_encode($app))) {
            throw new MarketplaceException('Could not write app meta file');
        }
    }

    private function moveToPublic(string $appDir, MarketplaceApp $app): void
    {
        $appsDir = $this->frameworkConfig->getAppsDir();

        $this->filesystem->rename($appDir, $appsDir . '/' . $app->getName());
    }

    private function moveToTrash(MarketplaceApp $app): void
    {
        $appsDir = $this->frameworkConfig->getAppsDir();
        $appDir = $appsDir . '/' . $app->getName();

        $this->filesystem->rename($appDir, $this->frameworkConfig->getPathCache($app->getName() . '_' . $app->getVersion() . '_' . uniqid()));
    }

    private function replaceVariables(string $appDir, MarketplaceApp $app, UserContext $context): void
    {
        $appKey = $this->getOrCreateAppKey($app, $context);
        $env = $this->getEnv($appKey);

        $files = [
            '.htaccess',
            'index.html',
        ];

        foreach ($files as $fileName) {
            $file = $appDir . '/' . $fileName;
            if (!is_file($file)) {
                continue;
            }

            $content = file_get_contents($file);

            foreach ($env as $key => $value) {
                if (is_scalar($value)) {
                    $content = str_replace('${' . $key . '}', (string) $value, $content);
                } else {
                    $content = str_replace('${' . $key . '}', '', $content);
                }
            }

            file_put_contents($file, $content);
        }
    }

    private function getOrCreateAppKey(MarketplaceApp $app, UserContext $context): string
    {
        $existing = $this->appTable->findOneByTenantAndName($context->getTenantId(), $app->getName() ?? '');
        if ($existing instanceof Table\Generated\AppRow) {
            return $existing->getAppKey();
        } else {
            $user = $this->userTable->findOneByTenantAndName($context->getTenantId(), 'Administrator');
            if (!$user instanceof Table\Generated\UserRow) {
                throw new MarketplaceException('Could not find default admin user');
            }

            $appCreate = new AppCreate();
            $appCreate->setUserId($user->getId());
            $appCreate->setStatus(1);
            $appCreate->setName($app->getName());
            $appCreate->setUrl($this->frameworkConfig->getAppsUrl() . '/' . $app->getName());
            $appCreate->setScopes($app->getScopes());
            $appId = $this->appService->create($appCreate, $context);

            $existing = $this->appTable->find($appId);
            if (!$existing instanceof Table\Generated\AppRow) {
                throw new MarketplaceException('Could not create app');
            }

            return $existing->getAppKey();
        }
    }

    private function getEnv(string $appKey): array
    {
        $apiUrl = $this->frameworkConfig->getDispatchUrl();
        $url = $this->frameworkConfig->getAppsUrl();
        $basePath = parse_url($url, PHP_URL_PATH);

        $env = array_merge($_ENV, [
            'API_URL' => $apiUrl,
            'URL' => $url,
            'BASE_PATH' => rtrim($basePath, '/'),
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

        return $env;
    }
}
