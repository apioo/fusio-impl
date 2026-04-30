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

namespace Fusio\Impl\Service\Marketplace\Action;

use Fusio\Impl\Service\Marketplace\RemoteAbstract;
use Fusio\Marketplace\MarketplaceAction;
use Fusio\Marketplace\MarketplaceActionCollection;
use Fusio\Marketplace\MarketplaceInstall;
use Fusio\Marketplace\MarketplaceMessageException;
use Sdkgen\Client\Exception\ClientException;

/**
 * Repository
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Repository extends RemoteAbstract
{
    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function fetchAll(int $startIndex = 0, ?string $query = null): MarketplaceActionCollection
    {
        return $this->getClient()->marketplace()->directory()->action()->getAll($startIndex, 16, $query);
    }

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function fetchByName(string $user, string $name): MarketplaceAction
    {
        return $this->getClient()->marketplace()->directory()->action()->get($user, $name);
    }

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    public function install(string $user, string $name): MarketplaceAction
    {
        $install = new MarketplaceInstall();
        $install->setName($user . '/' . $name);

        return $this->getClient()->marketplace()->directory()->action()->install($install);
    }
}
