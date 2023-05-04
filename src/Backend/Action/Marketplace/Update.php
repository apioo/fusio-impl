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
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Marketplace\Installer;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Update
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Update extends ActionAbstract
{
    private Installer $installerService;
    private ConfigInterface $config;

    public function __construct(RuntimeInterface $runtime, Installer $installerService, ConfigInterface $config)
    {
        parent::__construct($runtime);

        $this->installerService = $installerService;
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if (!$this->config->get('fusio_marketplace')) {
            throw new StatusCode\InternalServerErrorException('Marketplace is not enabled, please change the setting "fusio_marketplace" at the configuration.php to "true" in order to activate the marketplace');
        }

        $app = $this->installerService->update(
            $request->get('app_name'),
            UserContext::newActionContext($context)
        );

        return [
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successfully updated',
        ];
    }
}
