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

namespace Fusio\Impl\Service\Marketplace\Action;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Dto;
use Fusio\Impl\Dto\Marketplace\ObjectAbstract;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace\InstallerInterface;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use Fusio\Model\Common\Metadata;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Installer implements InstallerInterface
{
    private Service\Action $actionService;
    private Table\Action $actionTable;

    public function __construct(Service\Action $actionService, Table\Action $actionTable)
    {
        $this->actionService = $actionService;
        $this->actionTable = $actionTable;
    }

    public function install(ObjectAbstract $object, UserContext $context): void
    {
        if (!$object instanceof Dto\Marketplace\Action) {
            throw new \InvalidArgumentException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $create = new ActionCreate();
        $create->setName($object->getName());
        $create->setClass($object->getClass());
        $create->setConfig(ActionConfig::from($object->getConfig()));

        $metadata = new Metadata();
        $metadata->put('marketplace_version', $object->getVersion());
        $create->setMetadata($metadata);

        $this->actionService->create($create, $context);
    }

    public function upgrade(ObjectAbstract $object, UserContext $context): void
    {
        if (!$object instanceof Dto\Marketplace\Action) {
            throw new \InvalidArgumentException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $existing = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $object->getName());
        if (!$existing instanceof Table\Generated\ActionRow) {
            throw new \InvalidArgumentException('Provided an invalid action');
        }

        $update = new ActionUpdate();
        $update->setClass($object->getClass());
        $update->setConfig(ActionConfig::from($object->getConfig()));

        $metadata = $update->getMetadata() ?? new Metadata();
        $metadata->put('marketplace_version', $object->getVersion());
        $update->setMetadata($metadata);

        $this->actionService->update('' . $existing->getId(), $update, $context);
    }

    public function isInstalled(ObjectAbstract $object, UserContext $context): bool
    {
        $existing = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $object->getName());
        return $existing instanceof Table\Generated\ActionRow;
    }
}
