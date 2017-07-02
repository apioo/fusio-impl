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
        $this->log(
            $event->getContext(),
            $event->getActionId(),
            'action.create',
            sprintf('Created action %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getActionId(),
            'action.delete',
            sprintf('Deleted action %s', $event->getAction()['name'])
        );
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getActionId(),
            'action.update',
            sprintf('Updated action %s', $event->getAction()['name']),
            $event->getRecord()
        );
    }

    public function onAppCreate(Event\App\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.create',
            sprintf('Created app %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onAppDelete(Event\App\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.delete',
            sprintf('Deleted app %s', $event->getApp()['name'])
        );
    }

    public function onAppGenerateToken(Event\App\GeneratedTokenEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.generate_token',
            sprintf('Generated token for app'),
            ['appId' => $event->getAppId(), 'tokenId' => $event->getTokenId(), 'access_token' => $event->getAccessToken(), 'scope' => $event->getScopes(), 'expires' => $event->getExpires()->format('Y-m-d H:i:s'), 'now' => $event->getNow()->format('Y-m-d H:i:s')]
        );
    }

    public function onAppRemoveToken(Event\App\RemovedTokenEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.remove_token',
            sprintf('Removed token from app'),
            ['appId' => $event->getAppId(), 'tokenId' => $event->getTokenId()]
        );
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.update',
            sprintf('Updated app %s', $event->getApp()['name']),
            $event->getRecord()
        );
    }

    public function onConfigUpdate(Event\Config\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConfigId(),
            'config.update',
            sprintf('Updated config %s', $event->getRecord()['id']),
            $event->getRecord()
        );
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConnectionId(),
            'connection.create',
            sprintf('Created connection %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConnectionId(),
            'connection.delete',
            sprintf('Deleted connection %s', $event->getConnection()['name'])
        );
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConnectionId(),
            'connection.update',
            sprintf('Updated connection %s', $event->getConnection()['name']),
            $event->getRecord()
        );
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRateId(),
            'rate.create',
            sprintf('Created rate %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRateId(),
            'rate.delete',
            sprintf('Deleted rate %s', $event->getRate()['name'])
        );
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRateId(),
            'rate.update',
            sprintf('Updated rate %s', $event->getRate()['name']),
            $event->getRecord()
        );
    }

    public function onRoutesCreate(Event\Routes\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRouteId(),
            'routes.create',
            sprintf('Created route %s', $event->getRecord()['path']),
            $event->getRecord()
        );
    }

    public function onRoutesDelete(Event\Routes\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRouteId(),
            'routes.delete',
            sprintf('Deleted route %s', $event->getRoute()['path'])
        );
    }

    public function onRoutesDeploy(Event\Routes\DeployedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRouteId(),
            'routes.deploy',
            sprintf('Deployed method %s', $event->getMethod()['method'])
        );
    }

    public function onRoutesUpdate(Event\Routes\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRouteId(),
            'routes.update',
            sprintf('Updated route %s', $event->getRoute()['path']),
            $event->getRecord()
        );
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSchemaId(),
            'schema.create',
            sprintf('Created schema %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSchemaId(),
            'schema.delete',
            sprintf('Deleted schema %s', $event->getSchema()['name'])
        );
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSchemaId(),
            'schema.update',
            sprintf('Updated schema %s', $event->getSchema()['name']),
            $event->getRecord()
        );
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getScopeId(),
            'scope.create',
            sprintf('Created scope %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getScopeId(),
            'scope.delete',
            sprintf('Deleted scope %s', $event->getScope()['name'])
        );
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getScopeId(),
            'scope.update',
            sprintf('Updated scope %s', $event->getScope()['name']),
            $event->getRecord()
        );
    }

    public function onUserChangePassword(Event\User\ChangedPasswordEvent $event)
    {
        $this->log(
            $event->getContext(),
            null,
            'user.change_password',
            sprintf('Changed user password')
        );
    }

    public function onUserChangeStatus(Event\User\ChangedStatusEvent $event)
    {
        $this->log(
            $event->getContext(),
            null,
            'user.change_status',
            sprintf('Changed user status from %i to %i', $event->getOldStatus(), $event->getNewStatus())
        );
    }

    public function onUserCreate(Event\User\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getUserId(),
            'user.create',
            sprintf('Created user %s', $event->getRecord()['name']),
            $event->getRecord()
        );
    }

    public function onUserDelete(Event\User\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getUserId(),
            'user.delete',
            sprintf('Deleted user %s', $event->getUser()['name'])
        );
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getUserId(),
            'user.update',
            sprintf('Updated user %s', $event->getUser()['name']),
            $event->getRecord()
        );
    }

    private function log(UserContext $context, $refId, $event, $message, array $content = null)
    {
        $this->auditTable->create([
            'appId'   => $context->getAppId(),
            'userId'  => $context->getUserId(),
            'refId'   => $refId,
            'event'   => $event,
            'ip'      => $context->getIp(),
            'message' => $message,
            'content' => $this->normalize($content),
            'date'    => new \DateTime(),
        ]);
    }

    private function normalize(array $content = null)
    {
        if ($content !== null) {
            $result = new \stdClass();
            foreach ($content as $key => $value) {
                if ($value instanceof \DateTime) {
                    $result->{$key} = $value->format('Y-m-d H:i:s');
                } elseif ($key == 'password') {
                    $result->{$key} = '******';
                } elseif (in_array($key, ['cache', 'config'])) {
                    $result->{$key} = null;
                } else {
                    $result->{$key} = $value;
                }
            }
            return $result;
        } else {
            return null;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Event\ActionEvents::CREATE        => 'onActionCreate',
            Event\ActionEvents::DELETE        => 'onActionDelete',
            Event\ActionEvents::UPDATE        => 'onActionUpdate',

            Event\AppEvents::CREATE           => 'onAppCreate',
            Event\AppEvents::DELETE           => 'onAppDelete',
            Event\AppEvents::GENERATE_TOKEN   => 'onAppGenerateToken',
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
