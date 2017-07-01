<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * AuditListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AuditListener implements EventSubscriberInterface
{
    protected $auditTable;

    public function __construct(Table\Audit $auditTable)
    {
        $this->auditTable = $auditTable;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'action.create');
    }

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'action.delete');
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'action.update');
    }

    public function onAppCreate(Event\App\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'app.create');
    }

    public function onAppDelete(Event\App\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'app.delete');
    }

    public function onAppRemoveToken(Event\App\RemovedTokenEvent $event)
    {
        $this->log($event->getContext(), 'app.remove_token');
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'app.update');
    }

    public function onConfigUpdate(Event\Config\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'config.update');
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'connection.create');
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'connection.delete');
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'connection.update');
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'rate.create');
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'rate.delete');
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'rate.update');
    }

    public function onRoutesCreate(Event\Routes\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'routes.create');
    }

    public function onRoutesDelete(Event\Routes\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'routes.delete');
    }

    public function onRoutesDeploy(Event\Routes\DeployedEvent $event)
    {
        $this->log($event->getContext(), 'routes.deploy');
    }

    public function onRoutesUpdate(Event\Routes\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'routes.update');
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'schema.create');
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'schema.delete');
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'schema.update');
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'scope.create');
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'scope.delete');
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'scope.update');
    }

    public function onUserChangePassword(Event\User\ChangedPasswordEvent $event)
    {
        $this->log($event->getContext(), 'user.change_password');
    }

    public function onUserChangeStatus(Event\User\ChangedStatusEvent $event)
    {
        $this->log($event->getContext(), 'user.change_status');
    }

    public function onUserCreate(Event\User\CreatedEvent $event)
    {
        $this->log($event->getContext(), 'user.create');
    }

    public function onUserDelete(Event\User\DeletedEvent $event)
    {
        $this->log($event->getContext(), 'user.delete');
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event)
    {
        $this->log($event->getContext(), 'user.update');
    }

    private function log(UserContext $context, $event)
    {
        $this->auditTable->create([
            'appId'  => $context->getAppId(),
            'userId' => $context->getUserId(),
            'event'  => $event,
            'ip'     => $context->getIp(),
            'date'   => new \DateTime(),
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            Event\ActionEvents::CREATE        => 'onActionCreate',
            Event\ActionEvents::DELETE        => 'onActionDelete',
            Event\ActionEvents::UPDATE        => 'onActionUpdate',

            Event\AppEvents::CREATE           => 'onAppCreate',
            Event\AppEvents::DELETE           => 'onAppDelete',
            Event\AppEvents::REMOVE_TOKEN     => 'onAppRemoveToken',
            Event\AppEvents::UPDATE           => 'onAppUpdate',

            Event\ConfigEvents::UPDATE        => 'onConfigUpdate',

            Event\ConnectionEvents::CREATE    => 'onConnectionCreate',
            Event\ConnectionEvents::DELETE    => 'onConnectionDelete',
            Event\ConnectionEvents::UPDATE    => 'onConnectionUpdate',

            Event\RateEvents::CREATE          => 'onRateCreate',
            Event\RateEvents::DELETE          => 'onRateDelete',
            Event\RateEvents::UPDATE          => 'onRateUpdate',

            Event\RoutesEvents::CREATE        => 'onRoutesCreate',
            Event\RoutesEvents::DELETE        => 'onRoutesDelete',
            Event\RoutesEvents::DEPLOY        => 'onRoutesDeploy',
            Event\RoutesEvents::UPDATE        => 'onRoutesUpdate',

            Event\SchemaEvents::CREATE        => 'onSchemaCreate',
            Event\SchemaEvents::DELETE        => 'onSchemaDelete',
            Event\SchemaEvents::UPDATE        => 'onSchemaUpdate',

            Event\ScopeEvents::CREATE         => 'onScopeCreate',
            Event\ScopeEvents::DELETE         => 'onScopeDelete',
            Event\ScopeEvents::UPDATE         => 'onScopeUpdate',

            Event\UserEvents::CHANGE_PASSWORD => 'onUserChangePassword',
            Event\UserEvents::CHANGE_STATUS   => 'onUserChangeStatus',
            Event\UserEvents::CREATE          => 'onUserCreate',
            Event\UserEvents::DELETE          => 'onUserDelete',
            Event\UserEvents::UPDATE          => 'onUserUpdate',
        ];
    }
}
