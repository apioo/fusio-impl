<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class WebhookListener implements EventSubscriberInterface
{
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event)
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

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/action/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.action.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.action.delete', $event);
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
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

    public function onAppCreate(Event\App\CreatedEvent $event)
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

    public function onAppDelete(Event\App\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/app/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.app.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.app.delete', $event);
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event)
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

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
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

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/connection/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.connection.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.connection.delete', $event);
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
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

    public function onCronjobCreate(Event\Cronjob\CreatedEvent $event)
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

    public function onCronjobDelete(Event\Cronjob\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/cronjob/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.cronjob.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.cronjob.delete', $event);
    }

    public function onCronjobUpdate(Event\Cronjob\UpdatedEvent $event)
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

    public function onEventCreate(Event\Event\CreatedEvent $event)
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

    public function onEventDelete(Event\Event\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.event.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.event.delete', $event);
    }

    public function onEventUpdate(Event\Event\UpdatedEvent $event)
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

    public function onEventSubscriptionCreate(Event\Event\Subscription\CreatedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/subscription')
            ->withType('org.fusio-project.event.create')
            ->withDataContentType('application/json')
            ->withData($event->getSubscription())
            ->build();

        $this->dispatcher->dispatch('fusio.event.subscription.create', $event);
    }

    public function onEventSubscriptionDelete(Event\Event\Subscription\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/subscription/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.event.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.event.subscription.delete', $event);
    }

    public function onEventSubscriptionUpdate(Event\Event\Subscription\UpdatedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/event/subscription/' . $event->getSubscription()->getId())
            ->withType('org.fusio-project.event.update')
            ->withDataContentType('application/json')
            ->withData($event->getSubscription())
            ->build();

        $this->dispatcher->dispatch('fusio.event.subscription.update', $event);
    }

    public function onPlanCreate(Event\Plan\CreatedEvent $event)
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

    public function onPlanDelete(Event\Plan\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/plan/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.plan.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.plan.delete', $event);
    }

    public function onPlanUpdate(Event\Plan\UpdatedEvent $event)
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

    public function onRateCreate(Event\Rate\CreatedEvent $event)
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

    public function onRateDelete(Event\Rate\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/rate/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.rate.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.rate.delete', $event);
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event)
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

    public function onRouteCreate(Event\Route\CreatedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/routes')
            ->withType('org.fusio-project.route.create')
            ->withDataContentType('application/json')
            ->withData($event->getRoute())
            ->build();

        $this->dispatcher->dispatch('fusio.route.create', $event);
    }

    public function onRouteDelete(Event\Route\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/routes/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.route.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.route.delete', $event);
    }

    public function onRouteUpdate(Event\Route\UpdatedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/routes/' . $event->getRoute()->getId())
            ->withType('org.fusio-project.route.update')
            ->withDataContentType('application/json')
            ->withData($event->getRoute())
            ->build();

        $this->dispatcher->dispatch('fusio.route.update', $event);
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event)
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

    public function onSchemaDelete(Event\Schema\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/schema/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.schema.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.schema.delete', $event);
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event)
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

    public function onScopeCreate(Event\Scope\CreatedEvent $event)
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

    public function onScopeDelete(Event\Scope\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/scope/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.scope.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.scope.delete', $event);
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event)
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

    public function onUserCreate(Event\User\CreatedEvent $event)
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

    public function onUserDelete(Event\User\DeletedEvent $event)
    {
        $event = (new Builder())
            ->withId(Uuid::pseudoRandom())
            ->withSource('/backend/user/' . $event->getExisting()->getProperty('id'))
            ->withType('org.fusio-project.user.delete')
            ->withDataContentType('application/json')
            ->withData($event->getExisting())
            ->build();

        $this->dispatcher->dispatch('fusio.user.delete', $event);
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event)
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

    public static function getSubscribedEvents()
    {
        return [
            Event\Action\CreatedEvent::class        => 'onActionCreate',
            Event\Action\DeletedEvent::class        => 'onActionDelete',
            Event\Action\UpdatedEvent::class        => 'onActionUpdate',

            Event\App\CreatedEvent::class           => 'onAppCreate',
            Event\App\DeletedEvent::class           => 'onAppDelete',
            Event\App\UpdatedEvent::class           => 'onAppUpdate',

            Event\Connection\CreatedEvent::class    => 'onConnectionCreate',
            Event\Connection\DeletedEvent::class    => 'onConnectionDelete',
            Event\Connection\UpdatedEvent::class    => 'onConnectionUpdate',

            Event\Cronjob\CreatedEvent::class       => 'onCronjobCreate',
            Event\Cronjob\DeletedEvent::class       => 'onCronjobDelete',
            Event\Cronjob\UpdatedEvent::class       => 'onCronjobUpdate',

            Event\Event\CreatedEvent::class         => 'onEventCreate',
            Event\Event\DeletedEvent::class         => 'onEventDelete',
            Event\Event\UpdatedEvent::class         => 'onEventUpdate',

            Event\Event\Subscription\CreatedEvent::class => 'onEventSubscriptionCreate',
            Event\Event\Subscription\DeletedEvent::class => 'onEventSubscriptionDelete',
            Event\Event\Subscription\UpdatedEvent::class => 'onEventSubscriptionUpdate',

            Event\Plan\CreatedEvent::class          => 'onPlanCreate',
            Event\Plan\DeletedEvent::class          => 'onPlanDelete',
            Event\Plan\UpdatedEvent::class          => 'onPlanUpdate',

            Event\Rate\CreatedEvent::class          => 'onRateCreate',
            Event\Rate\DeletedEvent::class          => 'onRateDelete',
            Event\Rate\UpdatedEvent::class          => 'onRateUpdate',

            Event\Route\CreatedEvent::class         => 'onRouteCreate',
            Event\Route\DeletedEvent::class         => 'onRouteDelete',
            Event\Route\UpdatedEvent::class         => 'onRouteUpdate',

            Event\Schema\CreatedEvent::class        => 'onSchemaCreate',
            Event\Schema\DeletedEvent::class        => 'onSchemaDelete',
            Event\Schema\UpdatedEvent::class        => 'onSchemaUpdate',

            Event\Scope\CreatedEvent::class         => 'onScopeCreate',
            Event\Scope\DeletedEvent::class         => 'onScopeDelete',
            Event\Scope\UpdatedEvent::class         => 'onScopeUpdate',

            Event\User\CreatedEvent::class          => 'onUserCreate',
            Event\User\DeletedEvent::class          => 'onUserDelete',
            Event\User\UpdatedEvent::class          => 'onUserUpdate',
        ];
    }
}
