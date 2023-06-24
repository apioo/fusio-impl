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

namespace Fusio\Impl\EventListener;

use Fusio\Impl\Event;
use Fusio\Impl\Service\Event\Dispatcher;
use PSX\CloudEvents\Builder;
use PSX\Framework\Util\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * WebhookListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WebhookListener implements EventSubscriberInterface
{
    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/action')
            ->withType('org.fusio-project.action.create')
            ->withDataContentType('application/json')
            ->withData($event->getAction())
            ->build();

        $this->dispatcher->dispatch('fusio.action.create', $event);
    }

    public function onActionDelete(Event\Action\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/action/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.action.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.action.delete', $event);
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/action/' . $event->getAction()->getId())
            ->withType('org.fusio-project.action.update')
            ->withDataContentType('application/json')
            ->withData($event->getAction())
            ->build();

        $this->dispatcher->dispatch('fusio.action.update', $event);
    }

    public function onAppCreate(Event\App\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/app')
            ->withType('org.fusio-project.app.create')
            ->withDataContentType('application/json')
            ->withData($event->getApp())
            ->build();

        $this->dispatcher->dispatch('fusio.app.create', $event);
    }

    public function onAppDelete(Event\App\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/app/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.app.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.app.delete', $event);
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/app/' . $event->getApp()->getId())
            ->withType('org.fusio-project.app.update')
            ->withDataContentType('application/json')
            ->withData($event->getApp())
            ->build();

        $this->dispatcher->dispatch('fusio.app.update', $event);
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/connection')
            ->withType('org.fusio-project.connection.create')
            ->withDataContentType('application/json')
            ->withData($event->getConnection())
            ->build();

        $this->dispatcher->dispatch('fusio.connection.create', $event);
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/connection/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.connection.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.connection.delete', $event);
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/connection/' . $event->getConnection()->getId())
            ->withType('org.fusio-project.connection.update')
            ->withDataContentType('application/json')
            ->withData($event->getConnection())
            ->build();

        $this->dispatcher->dispatch('fusio.connection.update', $event);
    }

    public function onCronjobCreate(Event\Cronjob\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/cronjob')
            ->withType('org.fusio-project.cronjob.create')
            ->withDataContentType('application/json')
            ->withData($event->getCronjob())
            ->build();

        $this->dispatcher->dispatch('fusio.cronjob.create', $event);
    }

    public function onCronjobDelete(Event\Cronjob\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/cronjob/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.cronjob.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.cronjob.delete', $event);
    }

    public function onCronjobUpdate(Event\Cronjob\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/cronjob/' . $event->getCronjob()->getId())
            ->withType('org.fusio-project.cronjob.update')
            ->withDataContentType('application/json')
            ->withData($event->getCronjob())
            ->build();

        $this->dispatcher->dispatch('fusio.cronjob.update', $event);
    }

    public function onEventCreate(Event\Event\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event')
            ->withType('org.fusio-project.event.create')
            ->withDataContentType('application/json')
            ->withData($event->getEvent())
            ->build();

        $this->dispatcher->dispatch('fusio.event.create', $event);
    }

    public function onEventDelete(Event\Event\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.event.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.event.delete', $event);
    }

    public function onEventUpdate(Event\Event\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/' . $event->getEvent()->getId())
            ->withType('org.fusio-project.event.update')
            ->withDataContentType('application/json')
            ->withData($event->getEvent())
            ->build();

        $this->dispatcher->dispatch('fusio.event.update', $event);
    }

    public function onPlanCreate(Event\Plan\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/plan')
            ->withType('org.fusio-project.plan.create')
            ->withDataContentType('application/json')
            ->withData($event->getPlan())
            ->build();

        $this->dispatcher->dispatch('fusio.plan.create', $event);
    }

    public function onPlanDelete(Event\Plan\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/plan/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.plan.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.plan.delete', $event);
    }

    public function onPlanUpdate(Event\Plan\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/plan/' . $event->getPlan()->getId())
            ->withType('org.fusio-project.plan.update')
            ->withDataContentType('application/json')
            ->withData($event->getPlan())
            ->build();

        $this->dispatcher->dispatch('fusio.plan.update', $event);
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/rate')
            ->withType('org.fusio-project.rate.create')
            ->withDataContentType('application/json')
            ->withData($event->getRate())
            ->build();

        $this->dispatcher->dispatch('fusio.rate.create', $event);
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/rate/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.rate.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.rate.delete', $event);
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/rate/' . $event->getRate()->getId())
            ->withType('org.fusio-project.rate.update')
            ->withDataContentType('application/json')
            ->withData($event->getRate())
            ->build();

        $this->dispatcher->dispatch('fusio.rate.update', $event);
    }

    public function onOperationCreate(Event\Operation\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/operation')
            ->withType('org.fusio-project.operation.create')
            ->withDataContentType('application/json')
            ->withData($event->getOperation())
            ->build();

        $this->dispatcher->dispatch('fusio.operation.create', $event);
    }

    public function onOperationDelete(Event\Operation\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/operation/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.operation.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.operation.delete', $event);
    }

    public function onOperationUpdate(Event\Operation\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/operation/' . $event->getOperation()->getId())
            ->withType('org.fusio-project.operation.update')
            ->withDataContentType('application/json')
            ->withData($event->getOperation())
            ->build();

        $this->dispatcher->dispatch('fusio.operation.update', $event);
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/schema')
            ->withType('org.fusio-project.schema.create')
            ->withDataContentType('application/json')
            ->withData($event->getSchema())
            ->build();

        $this->dispatcher->dispatch('fusio.schema.create', $event);
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/schema/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.schema.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.schema.delete', $event);
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/schema/' . $event->getSchema()->getId())
            ->withType('org.fusio-project.schema.update')
            ->withDataContentType('application/json')
            ->withData($event->getSchema())
            ->build();

        $this->dispatcher->dispatch('fusio.schema.update', $event);
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/scope')
            ->withType('org.fusio-project.scope.create')
            ->withDataContentType('application/json')
            ->withData($event->getScope())
            ->build();

        $this->dispatcher->dispatch('fusio.scope.create', $event);
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/scope/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.scope.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.scope.delete', $event);
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/scope/' . $event->getScope()->getId())
            ->withType('org.fusio-project.scope.update')
            ->withDataContentType('application/json')
            ->withData($event->getScope())
            ->build();

        $this->dispatcher->dispatch('fusio.scope.update', $event);
    }

    public function onUserCreate(Event\User\CreatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/user')
            ->withType('org.fusio-project.user.create')
            ->withDataContentType('application/json')
            ->withData($event->getUser())
            ->build();

        $this->dispatcher->dispatch('fusio.user.create', $event);
    }

    public function onUserDelete(Event\User\DeletedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/user/' . $event->getExisting()->getId())
            ->withType('org.fusio-project.user.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.user.delete', $event);
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event): void
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/user/' . $event->getUser()->getId())
            ->withType('org.fusio-project.user.update')
            ->withDataContentType('application/json')
            ->withData($event->getUser())
            ->build();

        $this->dispatcher->dispatch('fusio.user.update', $event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event\Action\CreatedEvent::class => 'onActionCreate',
            Event\Action\DeletedEvent::class => 'onActionDelete',
            Event\Action\UpdatedEvent::class => 'onActionUpdate',

            Event\App\CreatedEvent::class => 'onAppCreate',
            Event\App\DeletedEvent::class => 'onAppDelete',
            Event\App\UpdatedEvent::class => 'onAppUpdate',

            Event\Connection\CreatedEvent::class => 'onConnectionCreate',
            Event\Connection\DeletedEvent::class => 'onConnectionDelete',
            Event\Connection\UpdatedEvent::class => 'onConnectionUpdate',

            Event\Cronjob\CreatedEvent::class => 'onCronjobCreate',
            Event\Cronjob\DeletedEvent::class => 'onCronjobDelete',
            Event\Cronjob\UpdatedEvent::class => 'onCronjobUpdate',

            Event\Event\CreatedEvent::class => 'onEventCreate',
            Event\Event\DeletedEvent::class => 'onEventDelete',
            Event\Event\UpdatedEvent::class => 'onEventUpdate',

            Event\Plan\CreatedEvent::class => 'onPlanCreate',
            Event\Plan\DeletedEvent::class => 'onPlanDelete',
            Event\Plan\UpdatedEvent::class => 'onPlanUpdate',

            Event\Rate\CreatedEvent::class => 'onRateCreate',
            Event\Rate\DeletedEvent::class => 'onRateDelete',
            Event\Rate\UpdatedEvent::class => 'onRateUpdate',

            Event\Operation\CreatedEvent::class => 'onOperationCreate',
            Event\Operation\DeletedEvent::class => 'onOperationDelete',
            Event\Operation\UpdatedEvent::class => 'onOperationUpdate',

            Event\Schema\CreatedEvent::class => 'onSchemaCreate',
            Event\Schema\DeletedEvent::class => 'onSchemaDelete',
            Event\Schema\UpdatedEvent::class => 'onSchemaUpdate',

            Event\Scope\CreatedEvent::class => 'onScopeCreate',
            Event\Scope\DeletedEvent::class => 'onScopeDelete',
            Event\Scope\UpdatedEvent::class => 'onScopeUpdate',

            Event\User\CreatedEvent::class => 'onUserCreate',
            Event\User\DeletedEvent::class => 'onUserDelete',
            Event\User\UpdatedEvent::class => 'onUserUpdate',
        ];
    }
}
