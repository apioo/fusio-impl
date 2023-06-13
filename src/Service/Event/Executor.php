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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Service\Connection\Resolver;
use Fusio\Impl\Service\Marketplace\Repository\Local;
use Fusio\Impl\Table;
use Fusio\Impl\Webhook\Message;
use Fusio\Impl\Service\Event\SenderFactory;
use Fusio\Impl\Webhook\SenderInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Client\ClientInterface;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Executor
{
    public const MAX_ATTEMPTS = 3;

    private Table\Event\Trigger $triggerTable;
    private Table\Event\Subscription $subscriptionTable;
    private Table\Event\Response $responseTable;
    private ClientInterface $httpClient;
    private Resolver $resolver;
    private SenderFactory $senderFactory;

    public function __construct(Table\Event\Trigger $triggerTable, Table\Event\Subscription $subscriptionTable, Table\Event\Response $responseTable, ClientInterface $httpClient, Resolver $resolver, SenderFactory $senderFactory)
    {
        $this->triggerTable       = $triggerTable;
        $this->subscriptionTable  = $subscriptionTable;
        $this->responseTable      = $responseTable;
        $this->httpClient         = $httpClient;
        $this->resolver           = $resolver;
        $this->senderFactory      = $senderFactory;
    }

    public function execute(): void
    {
        $this->insertTriggerEntries();
        $this->executePendingResponses();
    }

    private function insertTriggerEntries(): void
    {
        $triggers = $this->triggerTable->getAllPending();

        foreach ($triggers as $trigger) {
            $subscriptions = $this->subscriptionTable->getSubscriptionsForEvent($trigger['event_id']);

            foreach ($subscriptions as $subscription) {
                $row = new Table\Generated\EventResponseRow();
                $row->setTriggerId($trigger['id']);
                $row->setSubscriptionId($subscription['id']);
                $row->setStatus(Table\Event\Response::STATUS_PENDING);
                $row->setAttempts(0);
                $row->setInsertDate(LocalDateTime::now());
                $this->responseTable->create($row);
            }

            $this->triggerTable->markDone($trigger['id']);
        }
    }

    private function executePendingResponses(): void
    {
        $dispatcher = $this->resolver->get(Resolver::TYPE_DISPATCHER);
        if (!$dispatcher) {
            $dispatcher = $this->httpClient;
        }

        $sender = $this->senderFactory->factory($dispatcher);
        if (!$sender instanceof SenderInterface) {
            throw new \RuntimeException('Could not find sender for dispatcher');
        }

        $responses = $this->responseTable->getAllPending();
        foreach ($responses as $resp) {
            try {
                $code = $sender->send($dispatcher, new Message($resp['endpoint'], $resp['payload']));

                $this->responseTable->setResponse($resp['id'], $code, $resp['attempts'], null, self::MAX_ATTEMPTS);
            } catch (\Exception $e) {
                $this->responseTable->setResponse($resp['id'], null, $resp['attempts'], $e->getMessage(), self::MAX_ATTEMPTS);
            }
        }
    }
}
