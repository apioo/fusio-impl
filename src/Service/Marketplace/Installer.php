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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Dto\Marketplace\ObjectAbstract;
use Fusio\Impl\Service;
use Fusio\Impl\Dto;
use Fusio\Model\Backend\MarketplaceInstall;
use PSX\Http\Exception as StatusCode;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Installer
{
    private Factory $factory;
    private Service\System\FrameworkConfig $frameworkConfig;

    public function __construct(Factory $factory, Service\System\FrameworkConfig $frameworkConfig)
    {
        $this->factory = $factory;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function install(string $type, MarketplaceInstall $install, UserContext $context): ObjectAbstract
    {
        $factory = $this->factory->factory(Type::from($type));

        $name = $install->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        $object = $factory->getRepository()->fetchByName($name);
        if (!$object instanceof ObjectAbstract) {
            throw new StatusCode\BadRequestException(ucfirst($type) . ' not available');
        }

        $installer = $factory->getInstaller();
        if ($installer->isInstalled($object, $context)) {
            throw new StatusCode\BadRequestException(ucfirst($type) . ' already installed');
        }

        $installer->install($object, $context);

        return $object;
    }

    public function upgrade(string $type, string $name, UserContext $context): ObjectAbstract
    {
        $factory = $this->factory->factory(Type::from($type));

        $object = $factory->getRepository()->fetchByName($name);
        if (!$object instanceof ObjectAbstract) {
            throw new StatusCode\BadRequestException(ucfirst($type) . ' not available');
        }

        $installer = $factory->getInstaller();
        if (!$installer->isInstalled($object, $context)) {
            throw new StatusCode\BadRequestException(ucfirst($type) . ' is not installed');
        }

        $installer->upgrade($object, $context);

        return $object;
    }
}
