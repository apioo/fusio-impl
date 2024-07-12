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

use Fusio\Impl\Service\Marketplace\RemoteAbstract;
use Fusio\Marketplace\MarketplaceApp;
use Fusio\Marketplace\MarketplaceAppCollection;
use Fusio\Marketplace\MarketplaceInstall;

/**
 * Repository
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Repository extends RemoteAbstract
{
    public function fetchAll(int $startIndex = 0, ?string $query = null): MarketplaceAppCollection
    {
        return $this->getClient()->marketplace()->directory()->app()->getAll($startIndex, 16, $query);
    }

    public function fetchByName(string $user, string $name): MarketplaceApp
    {
        return $this->getClient()->marketplace()->directory()->app()->get($user, $name);
    }

    public function install(string $user, string $name): MarketplaceApp
    {
        $install = new MarketplaceInstall();
        $install->setName($user . '/' . $name);

        return $this->getClient()->marketplace()->directory()->app()->install($install);
    }
}
