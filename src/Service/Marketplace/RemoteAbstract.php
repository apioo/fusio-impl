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

use Fusio\Impl\Dto\Marketplace\Collection;
use Fusio\Impl\Dto\Marketplace\ObjectAbstract;
use Fusio\Impl\Service;
use Fusio\Marketplace\Client;
use Sdkgen\Client\Credentials\Anonymous;

/**
 * Remote
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class RemoteAbstract implements RepositoryInterface
{
    private Service\Config $configService;

    public function __construct(Service\Config $configService)
    {
        $this->configService = $configService;
    }

    protected function getClient(): Client
    {
        $clientId = $this->configService->getValue('marketplace_client_id');
        $clientSecret = $this->configService->getValue('marketplace_client_secret');

        if (!empty($clientId) && !empty($clientSecret)) {
            return Client::build($clientId, $clientSecret);
        } else {
            return new Client('https://api.fusio-project.org', new Anonymous());
        }
    }
}
