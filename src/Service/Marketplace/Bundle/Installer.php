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

namespace Fusio\Impl\Service\Marketplace\Bundle;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Exception\MarketplaceException;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace\InstallerInterface;
use Fusio\Impl\Table;
use Fusio\Marketplace\MarketplaceBundle;
use Fusio\Marketplace\MarketplaceBundleAction;
use Fusio\Marketplace\MarketplaceBundleActionConfig;
use Fusio\Marketplace\MarketplaceBundleConfig;
use Fusio\Marketplace\MarketplaceBundleCronjob;
use Fusio\Marketplace\MarketplaceBundleEvent;
use Fusio\Marketplace\MarketplaceBundleSchema;
use Fusio\Marketplace\MarketplaceBundleTrigger;
use Fusio\Marketplace\MarketplaceObject;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use Fusio\Model\Backend\CronjobCreate;
use Fusio\Model\Backend\CronjobUpdate;
use Fusio\Model\Backend\EventCreate;
use Fusio\Model\Backend\EventUpdate;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaSource;
use Fusio\Model\Backend\SchemaUpdate;
use Fusio\Model\Backend\TriggerCreate;
use Fusio\Model\Backend\TriggerUpdate;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Installer implements InstallerInterface
{
    public function __construct(
        private Service\Action $actionService,
        private Service\Schema $schemaService,
        private Service\Event $eventService,
        private Service\Cronjob $cronjobService,
        private Service\Trigger $triggerService,
        private Table\Action $actionTable,
        private Table\Schema $schemaTable,
        private Table\Event $eventTable,
        private Table\Cronjob $cronjobTable,
        private Table\Trigger $triggerTable
    ) {
    }

    public function install(MarketplaceObject $object, UserContext $context): void
    {
        if (!$object instanceof MarketplaceBundle) {
            throw new MarketplaceException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $this->installOrUpdate($object, $context);
    }

    public function upgrade(MarketplaceObject $object, UserContext $context): void
    {
        if (!$object instanceof MarketplaceBundle) {
            throw new MarketplaceException('Provided an invalid object, got: ' . get_debug_type($object));
        }

        $this->installOrUpdate($object, $context);
    }

    public function isInstalled(MarketplaceObject $object, UserContext $context): bool
    {
        return false;
    }

    private function installOrUpdate(MarketplaceBundle $object, UserContext $context): void
    {
        $config = $object->getConfig();
        if (!$config instanceof MarketplaceBundleConfig) {
            throw new MarketplaceException('No bundle config available');
        }

        $actions = $config->getActions() ?? [];
        foreach ($actions as $action) {
            $this->installOrUpgradeAction($object, $action, $context);
        }

        $schemas = $config->getSchemas() ?? [];
        foreach ($schemas as $schema) {
            $this->installOrUpgradeSchema($object, $schema, $context);
        }

        $events = $config->getEvents() ?? [];
        foreach ($events as $event) {
            $this->installOrUpgradeEvent($object, $event, $context);
        }

        $cronjobs = $config->getCronjobs() ?? [];
        foreach ($cronjobs as $cronjob) {
            $this->installOrUpgradeCronjob($object, $cronjob, $context);
        }

        $triggers = $config->getTriggers() ?? [];
        foreach ($triggers as $trigger) {
            $this->installOrUpgradeTrigger($object, $trigger, $context);
        }
    }

    private function installOrUpgradeAction(MarketplaceObject $object, MarketplaceBundleAction $action, UserContext $context): void
    {
        $actionName = $this->getObjectName($object, $action->getName());

        $existing = $this->actionTable->findOneByTenantAndName($context->getTenantId(), null, $actionName);
        if (!$existing instanceof Table\Generated\ActionRow) {
            $create = new ActionCreate();
            $create->setName($actionName);
            $create->setClass($action->getClass());
            $create->setConfig($this->buildConfig($action->getConfig() ?? throw new MarketplaceException('Provided no action config')));

            $this->actionService->create($create, $context);
        } else {
            $update = new ActionUpdate();
            $update->setClass($action->getClass());
            $update->setConfig($this->buildConfig($action->getConfig() ?? throw new MarketplaceException('Provided no action config')));

            $this->actionService->update('' . $existing->getId(), $update, $context);
        }
    }

    private function installOrUpgradeSchema(MarketplaceObject $object, MarketplaceBundleSchema $schema, UserContext $context): void
    {
        $schemaName = $this->getObjectName($object, $schema->getName());

        $existing = $this->schemaTable->findOneByTenantAndName($context->getTenantId(), null, $schemaName);
        if (!$existing instanceof Table\Generated\SchemaRow) {
            $create = new SchemaCreate();
            $create->setName($schemaName);
            $create->setSource(SchemaSource::from($schema->getSource()));

            $this->schemaService->create($create, $context);
        } else {
            $update = new SchemaUpdate();
            $update->setSource(SchemaSource::from($schema->getSource()));

            $this->schemaService->update('' . $existing->getId(), $update, $context);
        }
    }

    private function installOrUpgradeEvent(MarketplaceObject $object, MarketplaceBundleEvent $event, UserContext $context): void
    {
        $eventName = $this->getObjectName($object, $event->getName());

        $existing = $this->eventTable->findOneByTenantAndName($context->getTenantId(), null, $eventName);
        if (!$existing instanceof Table\Generated\EventRow) {
            $create = new EventCreate();
            $create->setName($eventName);
            $create->setDescription($event->getDescription());
            $create->setSchema($event->getSchema());

            $this->eventService->create($create, $context);
        } else {
            $update = new EventUpdate();
            $update->setDescription($event->getDescription());
            $update->setSchema($event->getSchema());

            $this->eventService->update('' . $existing->getId(), $update, $context);
        }
    }

    private function installOrUpgradeCronjob(MarketplaceObject $object, MarketplaceBundleCronjob $cronjob, UserContext $context): void
    {
        $cronjobName = $this->getObjectName($object, $cronjob->getName());

        $existing = $this->cronjobTable->findOneByTenantAndName($context->getTenantId(), null, $cronjobName);
        if (!$existing instanceof Table\Generated\CronjobRow) {
            $create = new CronjobCreate();
            $create->setName($cronjobName);
            $create->setCron($cronjob->getCron());
            $create->setAction($cronjob->getAction());

            $this->cronjobService->create($create, $context);
        } else {
            $update = new CronjobUpdate();
            $update->setCron($cronjob->getCron());
            $update->setAction($cronjob->getAction());

            $this->cronjobService->update('' . $existing->getId(), $update, $context);
        }
    }

    private function installOrUpgradeTrigger(MarketplaceObject $object, MarketplaceBundleTrigger $trigger, UserContext $context): void
    {
        $triggerName = $this->getObjectName($object, $trigger->getName());

        $existing = $this->triggerTable->findOneByTenantAndName($context->getTenantId(), null, $triggerName);
        if (!$existing instanceof Table\Generated\TriggerRow) {
            $create = new TriggerCreate();
            $create->setName($triggerName);
            $create->setEvent($trigger->getEvent());
            $create->setAction($trigger->getAction());

            $this->triggerService->create($create, $context);
        } else {
            $update = new TriggerUpdate();
            $update->setEvent($trigger->getEvent());
            $update->setAction($trigger->getAction());

            $this->triggerService->update('' . $existing->getId(), $update, $context);
        }
    }

    private function getObjectName(MarketplaceObject $object, string $name): string
    {
        return $object->getAuthor()?->getName() . '-' . $object->getName() . '-' . $name;
    }

    private function buildConfig(MarketplaceBundleActionConfig $config): ActionConfig
    {
        $result = new ActionConfig();
        foreach ($config->getAll() as $key => $value) {
            // @TODO try to fix connection at config

            $result->put($key, $value);
        }

        return $result;
    }
}
