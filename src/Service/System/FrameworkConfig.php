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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL;
use Fusio\Impl\Exception\InvalidConfigurationException;
use PSX\Framework\Config\ConfigInterface;

/**
 * Cleaner
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class FrameworkConfig
{
    private ConfigInterface $config;
    private DBAL\Tools\DsnParser $parser;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->parser = new DBAL\Tools\DsnParser();
    }

    public function getExpireTokenInterval(): \DateInterval
    {
        return new \DateInterval($this->config->get('fusio_expire_token'));
    }

    public function getExpireRefreshInterval(): \DateInterval
    {
        return new \DateInterval($this->config->get('fusio_expire_refresh') ?? 'P3D');
    }

    public function getTenantId(): ?string
    {
        $tenantId = $this->config->get('fusio_tenant_id');
        if (empty($tenantId)) {
            return null;
        }

        return $tenantId;
    }

    public function getProjectKey(): string
    {
        return $this->config->get('fusio_project_key');
    }

    public function getActionExclude(): ?array
    {
        return $this->config->get('fusio_action_exclude');
    }

    public function getConnectionExclude(): ?array
    {
        return $this->config->get('fusio_connection_exclude');
    }

    public function getProviderFile(): string
    {
        return $this->config->get('fusio_provider');
    }

    public function getMailSender(): ?string
    {
        return $this->config->get('fusio_mail_sender');
    }

    public function isMarketplaceEnabled(): bool
    {
        return !!$this->config->get('fusio_marketplace');
    }

    public function getAppsUrl(): string
    {
        return $this->config->get('fusio_apps_url');
    }

    public function getAppsDir(): string
    {
        return $this->config->get('fusio_apps_dir') ?: $this->getPathPublic();
    }

    public function getUrl(...$pathFragment): string
    {
        return $this->config->get('psx_url') . (count($pathFragment) > 0 ? '/' . implode('/', $pathFragment) : '');
    }

    public function getDispatchUrl(...$pathFragment): string
    {
        return $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch') . (count($pathFragment) > 0 ? implode('/', $pathFragment) : '');
    }

    public function getPathCache(...$directoryFragment): string
    {
        return $this->config->get('psx_path_cache') . (count($directoryFragment) > 0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $directoryFragment) : '');
    }

    public function getPathPublic(...$directoryFragment): string
    {
        return $this->config->get('psx_path_public') . (count($directoryFragment) > 0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $directoryFragment) : '');
    }

    public function getPathApp(): string
    {
        return $this->config->get('psx_path_app');
    }

    public function getDoctrineConnectionParameters(): array
    {
        $connection = $this->config->get('psx_connection');
        if (is_string($connection)) {
            return $this->parser->parse($this->config->get('psx_connection'));
        } elseif (is_array($connection)) {
            return $connection;
        } else {
            throw new InvalidConfigurationException('The configured connection must contain a string or array');
        }
    }
}
