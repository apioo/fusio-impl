<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\Action\Marketplace;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Marketplace;
use Fusio\Impl\Dto\Marketplace\App;
use PSX\Framework\Config\ConfigInterface;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GetAll extends ActionAbstract
{
    private Marketplace\Repository\Remote $remoteRepository;
    private Marketplace\Repository\Local $localRepository;
    private ConfigInterface $config;

    public function __construct(RuntimeInterface $runtime, Marketplace\Repository\Remote $remoteRepository, Marketplace\Repository\Local $localRepository, ConfigInterface $config)
    {
        parent::__construct($runtime);

        $this->remoteRepository = $remoteRepository;
        $this->localRepository = $localRepository;
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if ($this->config->get('fusio_marketplace')) {
            $result = $this->fetchRemoteApps();
        } else {
            $result = $this->fetchLocalApps();
        }

        return [
            'apps' => $result
        ];
    }

    private function fetchRemoteApps(): array
    {
        $apps = $this->remoteRepository->fetchAll();
        $result = [];

        foreach ($apps as $remoteApp) {
            $app = $remoteApp->toArray();

            $localApp = $this->localRepository->fetchByName($remoteApp->getName());
            if ($localApp instanceof App) {
                $app['local'] = $localApp->toArray();
                $app['local']['startUrl'] = $this->config->get('fusio_apps_url') . '/' . $localApp->getName();
            }

            $result[$remoteApp->getName()] = $app;
        }

        return $result;
    }

    private function fetchLocalApps(): array
    {
        $apps = $this->localRepository->fetchAll();
        $result = [];

        foreach ($apps as $localApp) {
            $app = $localApp->toArray();
            $app['local'] = $localApp->toArray();
            $app['local']['startUrl'] = $this->config->get('fusio_apps_url') . '/' . $localApp->getName();

            $result[$localApp->getName()] = $app;
        }

        return $result;
    }
}
