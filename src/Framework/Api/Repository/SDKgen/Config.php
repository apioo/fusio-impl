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

namespace Fusio\Impl\Framework\Api\Repository\SDKgen;

use Fusio\Impl\Service;
use PSX\Api\Repository\SDKgen\ConfigInterface;
use PSX\Framework\Config\ConfigInterface as FrameworkConfigInterface;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
readonly class Config implements ConfigInterface
{
    public function __construct(private Service\Config $configService, private FrameworkConfigInterface $config)
    {
    }

    public function getClientId(): ?string
    {
        $clientId = $this->configService->getValue('sdkgen_client_id');
        if (!empty($clientId)) {
            return $clientId;
        }

        return $this->config->get('sdkgen_client_id');
    }

    public function getClientSecret(): ?string
    {
        $clientSecret = $this->configService->getValue('sdkgen_client_secret');
        if (!empty($clientSecret)) {
            return $clientSecret;
        }

        return $this->config->get('sdkgen_client_secret');
    }
}
