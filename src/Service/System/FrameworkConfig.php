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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL;
use Fusio\Impl\Exception\InvalidConfigurationException;
use PSX\Framework\Config\BaseUrlInterface;
use PSX\Framework\Config\ConfigInterface;

/**
 * Cleaner
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class FrameworkConfig
{
    private DBAL\Tools\DsnParser $parser;

    public function __construct(private ConfigInterface $config, private BaseUrlInterface $baseUrl)
    {
        $this->parser = new DBAL\Tools\DsnParser();
    }

    public function getFirewallIgnoreIp(): ?array
    {
        return $this->config->get('fusio_firewall_ignoreip');
    }

    public function getFirewallBanTime(): \DateInterval
    {
        return new \DateInterval($this->config->get('fusio_firewall_bantime') ?? 'PT5M');
    }

    public function getFirewallFindTime(): \DateInterval
    {
        return new \DateInterval($this->config->get('fusio_firewall_findtime') ?? 'PT2M');
    }

    public function getFirewallMaxRetry(): int
    {
        return $this->config->get('fusio_firewall_maxretry') ?? 32;
    }

    public function getFirewallCodes(): ?array
    {
        return $this->config->get('fusio_firewall_codes');
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

    public function isRegistrationEnabled(): bool
    {
        return $this->config->get('fusio_registration') !== false;
    }

    public function isConnectionEnabled(): bool
    {
        return $this->config->get('fusio_connection') !== false;
    }

    public function isMarketplaceEnabled(): bool
    {
        return $this->config->get('fusio_marketplace') !== false;
    }

    public function isMCPEnabled(): bool
    {
        return $this->config->get('fusio_mcp') === true;
    }

    public function getMCPQueueSize(): int
    {
        return $this->config->get('fusio_mcp_queue_size') ?? 500;
    }

    public function getMCPSessionTimeout(): int
    {
        return $this->config->get('fusio_mcp_timeout') ?? 1800;
    }

    public function getAppsUrl(): string
    {
        return $this->config->get('fusio_apps_url') ?: $this->getUrl('apps');
    }

    public function getAppsDir(): string
    {
        return $this->config->get('fusio_apps_dir') ?: $this->getPathPublic();
    }

    public function getUrl(...$pathFragment): string
    {
        return $this->baseUrl->getUrl() . (count($pathFragment) > 0 ? '/' . implode('/', $pathFragment) : '');
    }

    public function getDispatchUrl(...$pathFragment): string
    {
        return $this->baseUrl->getDispatchUrl() . (count($pathFragment) > 0 ? implode('/', $pathFragment) : '');
    }

    public function getPathCache(...$directoryFragment): string
    {
        return $this->config->get('psx_path_cache') . (count($directoryFragment) > 0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $directoryFragment) : '');
    }

    public function getPathPublic(...$directoryFragment): string
    {
        return $this->config->get('psx_path_public') . (count($directoryFragment) > 0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $directoryFragment) : '');
    }

    public function getPathResources(...$directoryFragment): string
    {
        return $this->config->get('psx_path_resources') . (count($directoryFragment) > 0 ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $directoryFragment) : '');
    }

    public function getPathApp(): string
    {
        return $this->config->get('psx_path_app');
    }

    public function getEnvironment(): string
    {
        return $this->config->get('psx_env');
    }

    public function isDebug(): bool
    {
        return $this->config->get('psx_debug');
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
