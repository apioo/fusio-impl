<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service\Marketplace\ClientFactory;
use Fusio\Impl\Service\Marketplace\RemoteAbstract;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Marketplace\MarketplaceApp;
use Fusio\Marketplace\MarketplaceAppCollection;
use Fusio\Marketplace\MarketplaceInstall;
use Fusio\Marketplace\MarketplaceMessageException;
use Fusio\Marketplace\MarketplaceUser;
use Sdkgen\Client\Exception\ClientException;

/**
 * Repository
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 *
 * @extends RemoteAbstract<MarketplaceApp>
 */
class Repository extends RemoteAbstract
{
    public function __construct(private readonly FrameworkConfig $frameworkConfig, ClientFactory $clientFactory)
    {
        parent::__construct($clientFactory);
    }

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function fetchAll(int $startIndex = 0, ?string $query = null): MarketplaceAppCollection
    {
        return $this->getClient()->marketplace()->directory()->app()->getAll($startIndex, 16, $query);
    }

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function fetchByName(string $user, string $name): MarketplaceApp
    {
        return $this->getClient()->marketplace()->directory()->app()->get($user, $name);
    }

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function install(string $user, string $name): MarketplaceApp
    {
        $install = new MarketplaceInstall();
        $install->setName($user . '/' . $name);

        try {
            return $this->getClient()->marketplace()->directory()->app()->install($install);
        } catch (ClientException $e) {
            if ($this->hasLocalApp($user, $name)) {
                return $this->newLocalApp($user, $name);
            } else {
                throw $e;
            }
        }
    }

    private function newLocalApp(string $user, string $name): MarketplaceApp
    {
        $author = new MarketplaceUser();
        $author->setName($user);

        $app = new MarketplaceApp();
        $app->setName($name);
        $app->setAuthor($author);
        return $app;
    }

    private function hasLocalApp(string $user, string $name): bool
    {
        $appsDir = $this->frameworkConfig->getAppsDir();
        $appDir = $appsDir . '/' . $this->getDirName($user, $name);

        return is_dir($appDir);
    }

    private function getDirName(string $user, string $name): string
    {
        if (empty($user) || $user === 'fusio') {
            return $name;
        } else {
            return $user . '-' . $name;
        }
    }
}
