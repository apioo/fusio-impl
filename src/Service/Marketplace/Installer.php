<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Marketplace\MarketplaceInstall;
use Fusio\Marketplace\MarketplaceMessageException;
use Fusio\Marketplace\MarketplaceObject;
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

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function install(Type $type, MarketplaceInstall $install, UserContext $context): MarketplaceObject
    {
        $factory = $this->factory->factory($type);

        $fullName = $install->getName();
        if (empty($fullName)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        $parts = explode('/', $fullName);
        $user = $parts[0] ?? throw new StatusCode\BadRequestException('Name not provided');
        $name = $parts[1] ?? throw new StatusCode\BadRequestException('User not provided');

        try {
            $object = $factory->getRepository()->install($user, $name);
        } catch (MarketplaceMessageException $e) {
            throw new StatusCode\BadRequestException('Could not install action: ' . $e->getPayload()->getMessage());
        }

        $installer = $factory->getInstaller();
        if ($installer->isInstalled($object, $context)) {
            throw new StatusCode\BadRequestException(ucfirst($type->value) . ' already installed');
        }

        $installer->install($object, $context);

        return $object;
    }

    public function upgrade(Type $type, string $user, string $name, UserContext $context): MarketplaceObject
    {
        $factory = $this->factory->factory($type);

        try {
            $object = $factory->getRepository()->install($user, $name);
        } catch (MarketplaceMessageException $e) {
            throw new StatusCode\BadRequestException('Could not install action: ' . $e->getPayload()->getMessage());
        }

        $installer = $factory->getInstaller();
        if (!$installer->isInstalled($object, $context)) {
            throw new StatusCode\BadRequestException(ucfirst($type->value) . ' is not installed');
        }

        $installer->upgrade($object, $context);

        return $object;
    }
}
