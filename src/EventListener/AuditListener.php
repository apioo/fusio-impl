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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event;
use Fusio\Impl\Table;
use Fusio\Model\Backend\UserCreate;
use PSX\DateTime\LocalDateTime;
use PSX\Record\Record;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * AuditListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuditListener implements EventSubscriberInterface
{
    private Table\Audit $auditTable;

    public function __construct(Table\Audit $auditTable)
    {
        $this->auditTable = $auditTable;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getAction()->getId(),
            'action.create',
            sprintf('Created action %s', $event->getAction()->getName() ?? ''),
            $event->getAction()
        );
    }

    public function onActionDelete(Event\Action\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'action.delete',
            sprintf('Deleted action %s', $event->getExisting()->getName())
        );
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getAction()->getId(),
            'action.update',
            sprintf('Updated action %s', $event->getAction()->getName() ?? ''),
            $event->getAction()
        );
    }

    public function onAppCreate(Event\App\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getApp()->getId(),
            'app.create',
            sprintf('Created app %s', $event->getApp()->getName() ?? ''),
            $event->getApp()
        );
    }

    public function onAppDelete(Event\App\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'app.delete',
            sprintf('Deleted app %s', $event->getExisting()->getName())
        );
    }

    public function onGenerateToken(Event\Token\GeneratedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getTokenId(),
            'token.generate',
            'Generated token',
            Record::fromArray([
                'tokenId' => $event->getTokenId(),
                'accessToken' => $event->getAccessToken(),
                'scope' => $event->getScopes(),
                'expires' => $event->getExpires()->format('Y-m-d H:i:s'),
                'now' => $event->getNow()->format('Y-m-d H:i:s')
            ])
        );
    }

    public function onRemoveToken(Event\Token\RemovedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getTokenId(),
            'token.remove',
            'Removed token',
            Record::fromArray([
                'tokenId' => $event->getTokenId()
            ])
        );
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getApp()->getId(),
            'app.update',
            sprintf('Updated app %s', $event->getApp()->getName() ?? ''),
            $event->getApp()
        );
    }

    public function onConfigUpdate(Event\Config\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getConfig()->getId(),
            'config.update',
            sprintf('Updated config %s', $event->getConfig()->getName() ?? ''),
            $event->getConfig()
        );
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getConnection()->getId(),
            'connection.create',
            sprintf('Created connection %s', $event->getConnection()->getName() ?? ''),
            $event->getConnection()
        );
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'connection.delete',
            sprintf('Deleted connection %s', $event->getExisting()->getName())
        );
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getConnection()->getId(),
            'connection.update',
            sprintf('Updated connection %s', $event->getConnection()->getName() ?? ''),
            $event->getConnection()
        );
    }

    public function onCronjobCreate(Event\Cronjob\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getCronjob()->getId(),
            'cronjob.create',
            sprintf('Created cronjob %s', $event->getCronjob()->getName() ?? ''),
            $event->getCronjob()
        );
    }

    public function onCronjobDelete(Event\Cronjob\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'cronjob.delete',
            sprintf('Deleted cronjob %s', $event->getExisting()->getName())
        );
    }

    public function onCronjobUpdate(Event\Cronjob\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getCronjob()->getId(),
            'cronjob.update',
            sprintf('Updated cronjob %s', $event->getCronjob()->getName() ?? ''),
            $event->getCronjob()
        );
    }

    public function onEventCreate(Event\Event\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getEvent()->getId(),
            'event.create',
            sprintf('Created event %s', $event->getEvent()->getName() ?? ''),
            $event->getEvent()
        );
    }

    public function onEventDelete(Event\Event\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'event.delete',
            sprintf('Deleted event %s', $event->getExisting()->getName())
        );
    }

    public function onEventUpdate(Event\Event\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getEvent()->getId(),
            'event.update',
            sprintf('Updated event %s', $event->getEvent()->getName() ?? ''),
            $event->getEvent()
        );
    }

    public function onEventSubscriptionCreate(Event\Event\Subscription\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getSubscription()->getId(),
            'event.subscription.create',
            sprintf('Created event subscription %s', $event->getSubscription()->getEndpoint() ?? ''),
            $event->getSubscription()
        );
    }

    public function onEventSubscriptionDelete(Event\Event\Subscription\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'event.subscription.delete',
            sprintf('Deleted event subscription %s', $event->getExisting()->getEndpoint())
        );
    }

    public function onEventSubscriptionUpdate(Event\Event\Subscription\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getSubscription()->getId(),
            'event.subscription.update',
            sprintf('Updated event subscription %s', $event->getSubscription()->getEndpoint() ?? ''),
            $event->getSubscription()
        );
    }

    public function onPlanCreate(Event\Plan\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getPlan()->getId(),
            'plan.create',
            sprintf('Created plan %s', $event->getPlan()->getName() ?? ''),
            $event->getPlan()
        );
    }

    public function onPlanDelete(Event\Plan\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'plan.delete',
            sprintf('Deleted plan %s', $event->getExisting()->getName())
        );
    }

    public function onPlanUpdate(Event\Plan\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getPlan()->getId(),
            'plan.update',
            sprintf('Updated plan %s', $event->getPlan()->getName() ?? ''),
            $event->getPlan()
        );
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getRate()->getId(),
            'rate.create',
            sprintf('Created rate %s', $event->getRate()->getName() ?? ''),
            $event->getRate()
        );
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'rate.delete',
            sprintf('Deleted rate %s', $event->getExisting()->getName())
        );
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getRate()->getId(),
            'rate.update',
            sprintf('Updated rate %s', $event->getRate()->getName() ?? ''),
            $event->getRate()
        );
    }

    public function onOperationCreate(Event\Operation\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getOperation()->getId(),
            'operation.create',
            sprintf('Created operation %s', $event->getOperation()->getName() ?? ''),
            $event->getOperation()
        );
    }

    public function onOperationDelete(Event\Operation\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'operation.delete',
            sprintf('Deleted operation %s', $event->getExisting()->getName())
        );
    }

    public function onOperationUpdate(Event\Operation\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getOperation()->getId(),
            'operation.update',
            sprintf('Updated operation %s', $event->getOperation()->getName() ?? ''),
            $event->getOperation()
        );
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getSchema()->getId(),
            'schema.create',
            sprintf('Created schema %s', $event->getSchema()->getName() ?? ''),
            $event->getSchema()
        );
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'schema.delete',
            sprintf('Deleted schema %s', $event->getExisting()->getName())
        );
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getSchema()->getId(),
            'schema.update',
            sprintf('Updated schema %s', $event->getSchema()->getName() ?? ''),
            $event->getSchema()
        );
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getScope()->getId(),
            'scope.create',
            sprintf('Created scope %s', $event->getScope()->getName() ?? ''),
            $event->getScope()
        );
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'scope.delete',
            sprintf('Deleted scope %s', $event->getExisting()->getName())
        );
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getScope()->getId(),
            'scope.update',
            sprintf('Updated scope %s', $event->getScope()->getName() ?? ''),
            $event->getScope()
        );
    }

    public function onUserChangePassword(Event\User\ChangedPasswordEvent $event): void
    {
        $this->log(
            $event->getContext(),
            null,
            'user.change_password',
            'Changed user password'
        );
    }

    public function onUserChangeStatus(Event\User\ChangedStatusEvent $event): void
    {
        $this->log(
            $event->getContext(),
            null,
            'user.change_status',
            sprintf('Changed user status from %s to %s', $event->getOldStatus(), $event->getNewStatus())
        );
    }

    public function onUserCreate(Event\User\CreatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getUser()->getId(),
            'user.create',
            sprintf('Created user %s', $event->getUser()->getName() ?? ''),
            $event->getUser()
        );
    }

    public function onUserDelete(Event\User\DeletedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getId(),
            'user.delete',
            sprintf('Deleted user %s', $event->getExisting()->getName())
        );
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event): void
    {
        $this->log(
            $event->getContext(),
            $event->getUser()->getId(),
            'user.update',
            sprintf('Updated user %s', $event->getUser()->getName() ?? ''),
            $event->getUser()
        );
    }

    private function log(UserContext $context, ?int $refId, string $event, string $message, ?object $content = null): void
    {
        $row = new Table\Generated\AuditRow();
        $row->setAppId($context->getAppId() ?? 0);
        $row->setUserId($context->getUserId());
        $row->setRefId($refId);
        $row->setEvent($event);
        $row->setIp($context->getIp());
        $row->setMessage($message);
        $row->setContent($this->normalize($content));
        $row->setDate(LocalDateTime::now());
        $this->auditTable->create($row);
    }

    private function normalize(?object $content = null): string
    {
        if ($content instanceof UserCreate) {
            $content->setPassword('******');
        }

        return json_encode($content);
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

            Event\Config\UpdatedEvent::class => 'onConfigUpdate',

            Event\Connection\CreatedEvent::class => 'onConnectionCreate',
            Event\Connection\DeletedEvent::class => 'onConnectionDelete',
            Event\Connection\UpdatedEvent::class => 'onConnectionUpdate',

            Event\Cronjob\CreatedEvent::class => 'onCronjobCreate',
            Event\Cronjob\DeletedEvent::class => 'onCronjobDelete',
            Event\Cronjob\UpdatedEvent::class => 'onCronjobUpdate',

            Event\Event\CreatedEvent::class => 'onEventCreate',
            Event\Event\DeletedEvent::class => 'onEventDelete',
            Event\Event\UpdatedEvent::class => 'onEventUpdate',

            Event\Event\Subscription\CreatedEvent::class => 'onEventSubscriptionCreate',
            Event\Event\Subscription\DeletedEvent::class => 'onEventSubscriptionDelete',
            Event\Event\Subscription\UpdatedEvent::class => 'onEventSubscriptionUpdate',

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

            Event\Token\GeneratedEvent::class => 'onTokenGenerate',
            Event\Token\RemovedEvent::class => 'onTokenRemove',

            Event\User\ChangedPasswordEvent::class => 'onUserChangePassword',
            Event\User\ChangedStatusEvent::class => 'onUserChangeStatus',
            Event\User\CreatedEvent::class => 'onUserCreate',
            Event\User\DeletedEvent::class => 'onUserDelete',
            Event\User\UpdatedEvent::class => 'onUserUpdate',
        ];
    }
}
