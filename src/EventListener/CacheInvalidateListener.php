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

namespace Fusio\Impl\EventListener;

use Fusio\Impl\Event;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Clears the global cache if specific entities are updated
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CacheInvalidateListener implements EventSubscriberInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function onOperationCreate(Event\Operation\CreatedEvent $event): void
    {
        $this->cache->clear();
    }

    public function onOperationDelete(Event\Operation\DeletedEvent $event): void
    {
        $this->cache->clear();
    }

    public function onOperationUpdate(Event\Operation\UpdatedEvent $event): void
    {
        $this->cache->clear();
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event): void
    {
        $this->cache->clear();
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event): void
    {
        $this->cache->clear();
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event): void
    {
        $this->cache->clear();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event\Operation\CreatedEvent::class => 'onOperationCreate',
            Event\Operation\DeletedEvent::class => 'onOperationDelete',
            Event\Operation\UpdatedEvent::class => 'onOperationUpdate',

            Event\Schema\CreatedEvent::class => 'onSchemaCreate',
            Event\Schema\DeletedEvent::class => 'onSchemaDelete',
            Event\Schema\UpdatedEvent::class => 'onSchemaUpdate',
        ];
    }
}
