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

use Doctrine\DBAL\Connection;
use Fusio\Impl\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SystemLogListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SystemLogListener implements EventSubscriberInterface
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event)
    {
    }

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
    {
    }

    public function onAppCreate(Event\App\CreatedEvent $event)
    {
    }

    public function onAppDelete(Event\App\DeletedEvent $event)
    {
    }

    public function onAppRemoveToken(Event\App\RemovedTokenEvent $event)
    {
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event)
    {
    }

    public function onConfigUpdate(Event\Config\UpdatedEvent $event)
    {
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
    {
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
    {
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event)
    {
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event)
    {
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event)
    {
    }

    public function onRoutesCreate(Event\Routes\CreatedEvent $event)
    {
    }

    public function onRoutesDelete(Event\Routes\DeletedEvent $event)
    {
    }

    public function onRoutesDeploy(Event\Routes\DeployedEvent $event)
    {
    }

    public function onRoutesUpdate(Event\Routes\UpdatedEvent $event)
    {
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event)
    {
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event)
    {
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event)
    {
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event)
    {
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event)
    {
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event)
    {
    }

    public function onUserAuthenticate(Event\User\AuthenticatedEvent $event)
    {
    }

    public function onUserChangePassword(Event\User\ChangedPasswordEvent $event)
    {
    }

    public function onUserChangeStatus(Event\User\ChangedStatusEvent $event)
    {
    }

    public function onUserCreate(Event\User\CreatedEvent $event)
    {
    }

    public function onUserDelete(Event\User\DeletedEvent $event)
    {
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event)
    {
    }

    private function log()
    {
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

            Event\UserEvents::AUTHENTICATE    => 'onUserAuthenticate',
            Event\UserEvents::CHANGE_PASSWORD => 'onUserChangePassword',
            Event\UserEvents::CHANGE_STATUS   => 'onUserChangeStatus',
            Event\UserEvents::CREATE          => 'onUserCreate',
            Event\UserEvents::DELETE          => 'onUserDelete',
            Event\UserEvents::UPDATE          => 'onUserUpdate',
        ];
    }
}
