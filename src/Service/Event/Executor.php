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

use Fusio\Engine\Model;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Base;
use Fusio\Impl\Event\Rate\CreatedEvent;
use Fusio\Impl\Event\Rate\DeletedEvent;
use Fusio\Impl\Event\Rate\UpdatedEvent;
use Fusio\Impl\Event\RateEvents;
use Fusio\Impl\Table;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Request;
use PSX\Http\ResponseInterface;
use PSX\Sql\Condition;
use PSX\Uri\Uri;
use PSX\Uri\Url;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Event\Trigger $triggerTable
     * @param \Fusio\Impl\Table\Event\Subscription $subscriptionTable
     * @param \Fusio\Impl\Table\Event\Response $responseTable
     * @param \PSX\Http\Client\ClientInterface $httpClient
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Event\Trigger $triggerTable, Table\Event\Subscription $subscriptionTable, Table\Event\Response $responseTable, ClientInterface $httpClient, EventDispatcherInterface $eventDispatcher)
    {
        $this->triggerTable      = $triggerTable;
        $this->subscriptionTable = $subscriptionTable;
        $this->responseTable     = $responseTable;
        $this->httpClient        = $httpClient;
        $this->eventDispatcher   = $eventDispatcher;
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
            $subscriptions = $this->subscriptionTable->getSubscriptionsForEvent($trigger['eventId']);
            
            foreach ($subscriptions as $subscription) {
                $now = new \DateTime();
                
                $this->responseTable->create([
                    'triggerId' => $trigger['id'],
                    'subscriptionId' => $subscription['id'],
                    'status' => Table\Event\Response::STATUS_PENDING,
                    'attempts' => 0,
                    'insertDate' => $now->format('Y-m-d H:i:s'),
                ]);
            }

            $this->triggerTable->markDone($trigger['id']);
        }
    }

    private function executePendingResponses()
    {
        $responses = $this->responseTable->getAllPending();

        foreach ($responses as $resp) {
            // mark response as exceeded in case max attempts is reached
            if ($resp['attempts'] > self::MAX_ATTEMPTS) {
                $this->responseTable->markExceeded($resp['id']);
                continue;
            }

            try {
                $headers = [
                    'Content-Type' => 'application/json',
                    'User-Agent'   => Base::getUserAgent(),
                ];

                $request  = new Request(new Url($resp['endpoint']), 'POST', $headers, $resp['payload']);
                $response = $this->httpClient->request($request);

                if (($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) || $response->getStatusCode() == 410) {
                    $this->responseTable->markDone($resp['id']);
                } else {
                    // wrong response status code try again later
                    $this->responseTable->increaseAttempt($resp['id']);
                }
            } catch (\Exception $e) {
                // an error occurred try again later
                $this->responseTable->increaseAttempt($resp['id']);
            }
        }
    }
}
