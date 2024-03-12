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

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Exception as StatusCode;

/**
 * Remove
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Remove implements ActionInterface
{
    private Installer $installerService;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Installer $installerService, FrameworkConfig $frameworkConfig)
    {
        $this->installerService = $installerService;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if (!$this->frameworkConfig->isMarketplaceEnabled()) {
            throw new StatusCode\InternalServerErrorException('Marketplace is not enabled, please change the setting "fusio_marketplace" at the configuration.php to "true" in order to activate the marketplace');
        }

        $app = $this->installerService->remove(
            $request->get('app_name'),
            UserContext::newActionContext($context)
        );

        return [
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successful removed',
        ];
    }
}
