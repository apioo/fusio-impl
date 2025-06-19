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

namespace Fusio\Impl\Backend\Action\Marketplace;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Impl\Service\Marketplace\Type;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Marketplace\MarketplaceInstall;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception as StatusCode;

/**
 * InstallAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class InstallAbstract implements ActionInterface
{
    private Installer $installerService;
    private FrameworkConfig $frameworkConfig;
    private ContextFactory $contextFactory;

    public function __construct(Installer $installerService, FrameworkConfig $frameworkConfig, ContextFactory $contextFactory)
    {
        $this->installerService = $installerService;
        $this->frameworkConfig = $frameworkConfig;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if (!$this->frameworkConfig->isMarketplaceEnabled()) {
            throw new StatusCode\InternalServerErrorException('Marketplace is not enabled, please change the setting "fusio_marketplace" at the configuration.php to "true" in order to activate the marketplace');
        }

        $type = $this->getType();
        $body = $request->getPayload();

        assert($body instanceof MarketplaceInstall);

        $object = $this->installerService->install(
            $type,
            $body,
            $this->contextFactory->newActionContext($context)
        );

        return new HttpResponse(201, [], [
            'success' => true,
            'message' => ucfirst($type->value) . ' ' . $object->getName() . ' successfully installed',
        ]);
    }

    abstract protected function getType(): Type;
}
