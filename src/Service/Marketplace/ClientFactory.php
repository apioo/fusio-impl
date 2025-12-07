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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Service;
use Fusio\Marketplace\Client;
use Sdkgen\Client\Credentials\Anonymous;

/**
 * ClientFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ClientFactory
{
    public function __construct(private Service\Config $configService)
    {
    }

    public function factory(): Client
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
