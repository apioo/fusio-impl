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
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Model\Backend\MarketplaceInstall;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception as StatusCode;

/**
 * Install
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Install implements ActionInterface
{
    private Installer $installerService;
    private ConfigInterface $config;

    public function __construct(Installer $installerService, ConfigInterface $config)
    {
        $this->installerService = $installerService;
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if (!$this->config->get('fusio_marketplace')) {
            throw new StatusCode\InternalServerErrorException('Marketplace is not enabled, please change the setting "fusio_marketplace" at the configuration.php to "true" in order to activate the marketplace');
        }

        $body = $request->getPayload();

        assert($body instanceof MarketplaceInstall);

        $app = $this->installerService->install(
            $body,
            UserContext::newActionContext($context)
        );

        return new HttpResponse(201, [], [
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successful installed',
        ]);
    }
}
