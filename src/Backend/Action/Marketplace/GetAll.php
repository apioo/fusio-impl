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

namespace Fusio\Impl\Backend\Action\Marketplace;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ActionInterface;
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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetAll implements ActionInterface
{
    private Marketplace\Repository\Remote $remoteRepository;
    private Marketplace\Repository\Local $localRepository;
    private ConfigInterface $config;

    public function __construct(Marketplace\Repository\Remote $remoteRepository, Marketplace\Repository\Local $localRepository, ConfigInterface $config)
    {
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
