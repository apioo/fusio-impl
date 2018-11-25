<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Event;

use Fusio\Impl\Service\Connection\Resolver;
use Fusio\Impl\Table;
use PSX\Http\Client\ClientInterface;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Executor
{
    const MAX_ATTEMPTS = 3;

    /**
     * @var \Fusio\Impl\Table\Event\Trigger
     */
    protected $triggerTable;

    /**
     * @var \Fusio\Impl\Table\Event\Subscription
     */
    protected $subscriptionTable;

    /**
     * @var \Fusio\Impl\Table\Event\Response
     */
    protected $responseTable;

    /**
     * @var \PSX\Http\Client\ClientInterface
     */
    protected $httpClient;

    /**
     * @var \Fusio\Impl\Service\Connection\Resolver
     */
    protected $resolver;

    /**
     * @var \Fusio\Impl\Service\Event\SenderFactory
     */
    protected $senderFactory;

    /**
     * @param \Fusio\Impl\Table\Event\Trigger $triggerTable
     * @param \Fusio\Impl\Table\Event\Subscription $subscriptionTable
     * @param \Fusio\Impl\Table\Event\Response $responseTable
     * @param \PSX\Http\Client\ClientInterface $httpClient
     * @param \Fusio\Impl\Service\Connection\Resolver $resolver
     * @param \Fusio\Impl\Service\Event\SenderFactory $senderFactory
     */
    public function __construct(Table\Event\Trigger $triggerTable, Table\Event\Subscription $subscriptionTable, Table\Event\Response $responseTable, ClientInterface $httpClient, Resolver $resolver, SenderFactory $senderFactory)
    {
        $this->triggerTable       = $triggerTable;
        $this->subscriptionTable  = $subscriptionTable;
        $this->responseTable      = $responseTable;
        $this->httpClient         = $httpClient;
        $this->resolver           = $resolver;
        $this->senderFactory      = $senderFactory;
    }

    public function execute()
    {
        $this->insertTriggerEntries();
        $this->executePendingResponses();
    }

    private function insertTriggerEntries()
    {
        $triggers = $this->triggerTable->getAllPending();

        foreach ($triggers as $trigger) {
            $subscriptions = $this->subscriptionTable->getSubscriptionsForEvent($trigger['event_id']);

            foreach ($subscriptions as $subscription) {
                $now = new \DateTime();

                $this->responseTable->create([
                    'trigger_id' => $trigger['id'],
                    'subscription_id' => $subscription['id'],
                    'status' => Table\Event\Response::STATUS_PENDING,
                    'attempts' => 0,
                    'insert_date' => $now,
                ]);
            }

            $this->triggerTable->markDone($trigger['id']);
        }
    }

    private function executePendingResponses()
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

                $this->responseTable->setResponse($resp['id'], $code, $resp['attempts'], self::MAX_ATTEMPTS);
            } catch (\Exception $e) {
                $this->responseTable->setResponse($resp['id'], null, $resp['attempts'], self::MAX_ATTEMPTS);
            }
        }
    }
}
