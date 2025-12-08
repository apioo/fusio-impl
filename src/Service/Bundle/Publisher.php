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

namespace Fusio\Impl\Service\Bundle;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Marketplace\Client;
use Fusio\Marketplace\MarketplaceBundleAction;
use Fusio\Marketplace\MarketplaceBundleActionConfig;
use Fusio\Marketplace\MarketplaceBundleConfig;
use Fusio\Marketplace\MarketplaceBundleCreate;
use Fusio\Marketplace\MarketplaceBundleCronjob;
use Fusio\Marketplace\MarketplaceBundleEvent;
use Fusio\Marketplace\MarketplaceBundleSchema;
use Fusio\Marketplace\MarketplaceBundleSchemaSource;
use Fusio\Marketplace\MarketplaceBundleTrigger;
use Fusio\Marketplace\MarketplaceMessage;
use Fusio\Marketplace\MarketplaceMessageException;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use Sdkgen\Client\Exception\ClientException;

/**
 * Sender
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Publisher
{
    public function __construct(
        private Table\Bundle $bundleTable,
        private Service\Marketplace\ClientFactory $clientFactory,
        private Table\Action $actionTable,
        private Table\Schema $schemaTable,
        private Table\Event $eventTable,
        private Table\Cronjob $cronjobTable,
        private Table\Trigger $triggerTable,
    )
    {
    }

    public function publish(string $bundleId, UserContext $context): MarketplaceMessage
    {
        $bundle = $this->bundleTable->findOneByIdentifier($context->getTenantId(), $bundleId);
        if (!$bundle instanceof Table\Generated\BundleRow) {
            throw new StatusCode\NotFoundException('Provided bundle id does not exist');
        }

        if (!$this->clientFactory->isConfigured()) {
            throw new StatusCode\InternalServerErrorException('Please configure your marketplace credentials under System / Config in order to publish a bundle. If you have no credentials you can register an account at: https://www.fusio-project.org/marketplace');
        }

        try {
            $client = $this->clientFactory->factory();

            return $client->marketplace()->my()->bundle()->create($this->buildBundle($bundle, $context));
        } catch (MarketplaceMessageException $e) {
            return $e->getPayload();
        } catch (ClientException $e) {
            $message = new MarketplaceMessage();
            $message->setSuccess(false);
            $message->setMessage($e->getMessage());
            return $message;
        }
    }

    private function buildBundle(Table\Generated\BundleRow $bundle, UserContext $context): MarketplaceBundleCreate
    {
        $create = new MarketplaceBundleCreate();
        $create->setName($bundle->getName());
        $create->setVersion($bundle->getVersion());
        $create->setIcon($bundle->getIcon());
        $create->setSummary($bundle->getSummary());
        $create->setDescription($bundle->getDescription());
        $create->setCost($bundle->getCost());
        $create->setConfig($this->buildConfig($bundle, $context));

        return $create;
    }

    private function buildConfig(Table\Generated\BundleRow $bundle, UserContext $context): MarketplaceBundleConfig
    {
        $config = Parser::decode($bundle->getConfig());
        $result = new MarketplaceBundleConfig();

        if (isset($config['actions']) && is_array($config['actions'])) {
            $result->setActions($this->buildActions($config['actions'], $context));
        }

        if (isset($config['schemas']) && is_array($config['schemas'])) {
            $result->setSchemas($this->buildSchemas($config['actions'], $context));
        }

        if (isset($config['events']) && is_array($config['events'])) {
            $result->setEvents($this->buildEvents($config['actions'], $context));
        }

        if (isset($config['cronjobs']) && is_array($config['cronjobs'])) {
            $result->setCronjobs($this->buildCronjobs($config['actions'], $context));
        }

        if (isset($config['triggers']) && is_array($config['triggers'])) {
            $result->setTriggers($this->buildTriggers($config['actions'], $context));
        }

        return $result;
    }

    /**
     * @return array<MarketplaceBundleAction>
     */
    private function buildActions(array $actionIds, UserContext $context): array
    {
        $result = [];
        foreach ($actionIds as $actionId) {
            $actionRow = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $actionId);
            if (!$actionRow instanceof Table\Generated\ActionRow) {
                continue;
            }

            $rawConfig = $actionRow->getConfig();
            if (empty($rawConfig)) {
                continue;
            }

            $config = Parser::decode($rawConfig);
            if (!$config instanceof \stdClass) {
                continue;
            }

            $action = new MarketplaceBundleAction();
            $action->setName($actionRow->getName());
            $action->setClass($actionRow->getClass());
            $action->setConfig(MarketplaceBundleActionConfig::fromObject($config));

            $result[] = $action;
        }

        return $result;
    }

    /**
     * @return array<MarketplaceBundleSchema>
     */
    private function buildSchemas(array $schemaIds, UserContext $context): array
    {
        $result = [];
        foreach ($schemaIds as $schemaId) {
            $schemaRow = $this->schemaTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $schemaId);
            if (!$schemaRow instanceof Table\Generated\SchemaRow) {
                continue;
            }

            $rawSource = $schemaRow->getSource();
            if (empty($rawSource)) {
                continue;
            }

            $source = Parser::decode($rawSource);
            if (!$source instanceof \stdClass) {
                continue;
            }

            $schema = new MarketplaceBundleSchema();
            $schema->setName($schemaRow->getName());
            $schema->setSource(MarketplaceBundleSchemaSource::fromObject($source));

            $result[] = $schema;
        }

        return $result;
    }

    /**
     * @return array<MarketplaceBundleEvent>
     */
    private function buildEvents(array $eventIds, UserContext $context): array
    {
        $result = [];
        foreach ($eventIds as $eventId) {
            $eventRow = $this->eventTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $eventId);
            if (!$eventRow instanceof Table\Generated\EventRow) {
                continue;
            }

            $event = new MarketplaceBundleEvent();
            $event->setName($eventRow->getName());
            $event->setDescription($eventRow->getDescription());
            $event->setSchema($eventRow->getEventSchema());

            $result[] = $event;
        }

        return $result;
    }

    /**
     * @return array<MarketplaceBundleCronjob>
     */
    private function buildCronjobs(array $cronjobIds, UserContext $context): array
    {
        $result = [];
        foreach ($cronjobIds as $cronjobId) {
            $cronjobRow = $this->cronjobTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $cronjobId);
            if (!$cronjobRow instanceof Table\Generated\CronjobRow) {
                continue;
            }

            $cronjob = new MarketplaceBundleCronjob();
            $cronjob->setName($cronjobRow->getName());
            $cronjob->setCron($cronjobRow->getCron());
            $cronjob->setAction($cronjobRow->getAction());

            $result[] = $cronjob;
        }

        return $result;
    }

    /**
     * @return array<MarketplaceBundleTrigger>
     */
    private function buildTriggers(array $triggerIds, UserContext $context): array
    {
        $result = [];
        foreach ($triggerIds as $triggerId) {
            $triggerRow = $this->triggerTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $triggerId);
            if (!$triggerRow instanceof Table\Generated\TriggerRow) {
                continue;
            }

            $trigger = new MarketplaceBundleTrigger();
            $trigger->setName($triggerRow->getName());
            $trigger->setEvent($triggerRow->getEvent());
            $trigger->setAction($triggerRow->getAction());

            $result[] = $trigger;
        }

        return $result;
    }
}
