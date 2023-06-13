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
use PSX\Http\Exception as StatusCode;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Get implements ActionInterface
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
        $localApp = $this->localRepository->fetchByName(
            $request->get('app_name')
        );

        if (empty($localApp)) {
            throw new StatusCode\NotFoundException('Could not find local app');
        }

        if ($this->config->get('fusio_marketplace')) {
            $remoteApp = $this->remoteRepository->fetchByName(
                $request->get('app_name')
            );
        } else {
            $remoteApp = null;
        }

        $app = $localApp->toArray();
        if ($remoteApp instanceof App) {
            $app['remote'] = $remoteApp->toArray();
        }

        return $app;
    }
}
