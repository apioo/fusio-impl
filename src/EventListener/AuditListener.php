<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Backend\Model\User_Create;
use Fusio\Impl\Event;
use Fusio\Impl\Table;
use PSX\Record\Record;
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
            $event->getAction()->getId(),
            'action.create',
            sprintf('Created action %s', $event->getAction()->getName()),
            $event->getAction()
        );
    }

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'action.delete',
            sprintf('Deleted action %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAction()->getId(),
            'action.update',
            sprintf('Updated action %s', $event->getAction()->getName()),
            $event->getAction()
        );
    }

    public function onAppCreate(Event\App\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getApp()->getId(),
            'app.create',
            sprintf('Created app %s', $event->getApp()->getName()),
            $event->getApp()
        );
    }

    public function onAppDelete(Event\App\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'app.delete',
            sprintf('Deleted app %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onAppGenerateToken(Event\App\GeneratedTokenEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.generate_token',
            sprintf('Generated token for app'),
            Record::fromArray([
                'appId' => $event->getAppId(),
                'tokenId' => $event->getTokenId(),
                'accessToken' => $event->getAccessToken(),
                'scope' => $event->getScopes(),
                'expires' => $event->getExpires()->format('Y-m-d H:i:s'),
                'now' => $event->getNow()->format('Y-m-d H:i:s')
            ])
        );
    }

    public function onAppRemoveToken(Event\App\RemovedTokenEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getAppId(),
            'app.remove_token',
            sprintf('Removed token from app'),
            Record::fromArray([
                'appId' => $event->getAppId(),
                'tokenId' => $event->getTokenId()
            ])
        );
    }

    public function onAppUpdate(Event\App\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getApp()->getId(),
            'app.update',
            sprintf('Updated app %s', $event->getApp()->getName()),
            $event->getApp()
        );
    }

    public function onConfigUpdate(Event\Config\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConfig()->getId(),
            'config.update',
            sprintf('Updated config %s', $event->getConfig()->getId()),
            $event->getConfig()
        );
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConnection()->getId(),
            'connection.create',
            sprintf('Created connection %s', $event->getConnection()->getName()),
            $event->getConnection()
        );
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'connection.delete',
            sprintf('Deleted connection %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getConnection()->getId(),
            'connection.update',
            sprintf('Updated connection %s', $event->getConnection()->getName()),
            $event->getConnection()
        );
    }

    public function onCronjobCreate(Event\Cronjob\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getCronjob()->getId(),
            'cronjob.create',
            sprintf('Created cronjob %s', $event->getCronjob()->getName()),
            $event->getCronjob()
        );
    }

    public function onCronjobDelete(Event\Cronjob\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'cronjob.delete',
            sprintf('Deleted cronjob %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onCronjobUpdate(Event\Cronjob\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getCronjob()->getId(),
            'cronjob.update',
            sprintf('Updated cronjob %s', $event->getCronjob()->getName()),
            $event->getCronjob()
        );
    }

    public function onEventCreate(Event\Event\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getEvent()->getId(),
            'event.create',
            sprintf('Created event %s', $event->getEvent()->getName()),
            $event->getEvent()
        );
    }

    public function onEventDelete(Event\Event\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'event.delete',
            sprintf('Deleted event %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onEventUpdate(Event\Event\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getEvent()->getId(),
            'event.update',
            sprintf('Updated event %s', $event->getEvent()->getName()),
            $event->getEvent()
        );
    }

    public function onEventSubscriptionCreate(Event\Event\Subscription\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSubscription()->getId(),
            'event.subscription.create',
            sprintf('Created event subscription %s', $event->getSubscription()->getEndpoint()),
            $event->getSubscription()
        );
    }

    public function onEventSubscriptionDelete(Event\Event\Subscription\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'event.subscription.delete',
            sprintf('Deleted event subscription %s', $event->getExisting()->getProperty('endpoint'))
        );
    }

    public function onEventSubscriptionUpdate(Event\Event\Subscription\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSubscription()->getId(),
            'event.subscription.update',
            sprintf('Updated event subscription %s', $event->getSubscription()->getEndpoint()),
            $event->getSubscription()
        );
    }

    public function onPlanCreate(Event\Plan\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getPlan()->getId(),
            'plan.create',
            sprintf('Created plan %s', $event->getPlan()->getName()),
            $event->getPlan()
        );
    }

    public function onPlanDelete(Event\Plan\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'plan.delete',
            sprintf('Deleted plan %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onPlanUpdate(Event\Plan\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getPlan()->getId(),
            'plan.update',
            sprintf('Updated plan %s', $event->getPlan()->getName()),
            $event->getPlan()
        );
    }

    public function onPlanContractCreate(Event\Plan\Contract\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getContract()->getId(),
            'contract.create',
            'Created contract',
            $event->getContract()
        );
    }

    public function onPlanContractDelete(Event\Plan\Contract\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'contract.delete',
            'Deleted contract'
        );
    }

    public function onPlanContractUpdate(Event\Plan\Contract\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getContract()->getId(),
            'contract.update',
            'Updated contract',
            $event->getContract()
        );
    }

    public function onPlanInvoiceCreate(Event\Plan\Invoice\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getInvoice()->getId(),
            'invoice.create',
            'Created invoice',
            $event->getInvoice()
        );
    }

    public function onPlanInvoiceDelete(Event\Plan\Invoice\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'invoice.delete',
            'Deleted invoice'
        );
    }

    public function onPlanInvoiceUpdate(Event\Plan\Invoice\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getInvoice()->getId(),
            'invoice.update',
            'Updated invoice',
            $event->getInvoice()
        );
    }

    public function onPlanInvoicePayed(Event\Plan\Invoice\PayedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getInvoiceId(),
            'invoice.payed',
            'Payed invoice',
            (object) [
                'transactionId' => $event->getTransaction()->getId(),
                'transaction' => $event->getTransaction()->getTransactionId(),
            ]
        );
    }

    public function onRateCreate(Event\Rate\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRate()->getId(),
            'rate.create',
            sprintf('Created rate %s', $event->getRate()->getName()),
            $event->getRate()
        );
    }

    public function onRateDelete(Event\Rate\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'rate.delete',
            sprintf('Deleted rate %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onRateUpdate(Event\Rate\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRate()->getId(),
            'rate.update',
            sprintf('Updated rate %s', $event->getRate()->getName()),
            $event->getRate()
        );
    }

    public function onRouteCreate(Event\Route\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRoute()->getId(),
            'routes.create',
            sprintf('Created route %s', $event->getRoute()->getPath()),
            $event->getRoute()
        );
    }

    public function onRouteDelete(Event\Route\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'routes.delete',
            sprintf('Deleted route %s', $event->getExisting()->getProperty('path'))
        );
    }

    public function onRouteUpdate(Event\Route\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getRoute()->getId(),
            'routes.update',
            sprintf('Updated route %s', $event->getRoute()->getPath()),
            $event->getRoute()
        );
    }

    public function onSchemaCreate(Event\Schema\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSchema()->getId(),
            'schema.create',
            sprintf('Created schema %s', $event->getSchema()->getName()),
            $event->getSchema()
        );
    }

    public function onSchemaDelete(Event\Schema\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'schema.delete',
            sprintf('Deleted schema %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onSchemaUpdate(Event\Schema\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getSchema()->getId(),
            'schema.update',
            sprintf('Updated schema %s', $event->getSchema()->getName()),
            $event->getSchema()
        );
    }

    public function onScopeCreate(Event\Scope\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getScope()->getId(),
            'scope.create',
            sprintf('Created scope %s', $event->getScope()->getName()),
            $event->getScope()
        );
    }

    public function onScopeDelete(Event\Scope\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'scope.delete',
            sprintf('Deleted scope %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onScopeUpdate(Event\Scope\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getScope()->getId(),
            'scope.update',
            sprintf('Updated scope %s', $event->getScope()->getName()),
            $event->getScope()
        );
    }

    public function onTransactionPrepare(Event\Transaction\PreparedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getTransaction()->getId(),
            'transaction.prepare',
            sprintf('Prepared transaction %s', $event->getTransaction()->getTransactionId()),
            Record::fromArray([
                'invoiceId' => $event->getTransaction()->getInvoiceId(),
                'transactionId' => $event->getTransaction()->getTransactionId(),
                'provider' => $event->getTransaction()->getProvider(),
                'status' => $event->getTransaction()->getStatus(),
                'remoteId' => $event->getTransaction()->getRemoteId(),
                'amount' => $event->getTransaction()->getAmount(),
                'returnUrl' => $event->getTransaction()->getReturnUrl(),
            ])
        );
    }

    public function onTransactionExecute(Event\Transaction\ExecutedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getTransaction()->getId(),
            'transaction.execute',
            sprintf('Executed transaction %s', $event->getTransaction()->getTransactionId()),
            Record::fromArray([
                'invoiceId' => $event->getTransaction()->getInvoiceId(),
                'transactionId' => $event->getTransaction()->getTransactionId(),
                'provider' => $event->getTransaction()->getProvider(),
                'status' => $event->getTransaction()->getStatus(),
                'remoteId' => $event->getTransaction()->getRemoteId(),
                'amount' => $event->getTransaction()->getAmount(),
                'returnUrl' => $event->getTransaction()->getReturnUrl(),
            ])
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
            sprintf('Changed user status from %s to %s', $event->getOldStatus(), $event->getNewStatus())
        );
    }

    public function onUserCreate(Event\User\CreatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getUser()->getId(),
            'user.create',
            sprintf('Created user %s', $event->getUser()->getName()),
            $event->getUser()
        );
    }

    public function onUserDelete(Event\User\DeletedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getExisting()->getProperty('id'),
            'user.delete',
            sprintf('Deleted user %s', $event->getExisting()->getProperty('name'))
        );
    }

    public function onUserUpdate(Event\User\UpdatedEvent $event)
    {
        $this->log(
            $event->getContext(),
            $event->getUser()->getId(),
            'user.update',
            sprintf('Updated user %s', $event->getUser()->getName()),
            $event->getUser()
        );
    }

    private function log(UserContext $context, $refId, $event, $message, ?object $content = null)
    {
        $this->auditTable->create([
            'app_id'   => $context->getAppId(),
            'user_id'  => $context->getUserId(),
            'ref_id'   => $refId,
            'event'    => $event,
            'ip'       => $context->getIp(),
            'message'  => $message,
            'content'  => $this->normalize($content),
            'date'     => new \DateTime(),
        ]);
    }

    private function normalize(?object $content = null)
    {
        if ($content instanceof User_Create) {
            $content->setPassword('******');
        }

        return $content;
    }

    public static function getSubscribedEvents()
    {
        return [
            Event\Action\CreatedEvent::class        => 'onActionCreate',
            Event\Action\DeletedEvent::class        => 'onActionDelete',
            Event\Action\UpdatedEvent::class        => 'onActionUpdate',

            Event\App\CreatedEvent::class           => 'onAppCreate',
            Event\App\DeletedEvent::class           => 'onAppDelete',
            Event\App\GeneratedTokenEvent::class    => 'onAppGenerateToken',
            Event\App\RemovedTokenEvent::class      => 'onAppRemoveToken',
            Event\App\UpdatedEvent::class           => 'onAppUpdate',

            Event\Config\UpdatedEvent::class        => 'onConfigUpdate',

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

            Event\Plan\Contract\CreatedEvent::class => 'onPlanContractCreate',
            Event\Plan\Contract\DeletedEvent::class => 'onPlanContractDelete',
            Event\Plan\Contract\UpdatedEvent::class => 'onPlanContractUpdate',

            Event\Plan\Invoice\CreatedEvent::class  => 'onPlanInvoiceCreate',
            Event\Plan\Invoice\DeletedEvent::class  => 'onPlanInvoiceDelete',
            Event\Plan\Invoice\UpdatedEvent::class  => 'onPlanInvoiceUpdate',
            Event\Plan\Invoice\PayedEvent::class    => 'onPlanInvoicePayed',

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

            Event\Transaction\PreparedEvent::class  => 'onTransactionPrepare',
            Event\Transaction\ExecutedEvent::class  => 'onTransactionExecute',

            Event\User\ChangedPasswordEvent::class  => 'onUserChangePassword',
            Event\User\ChangedStatusEvent::class    => 'onUserChangeStatus',
            Event\User\CreatedEvent::class          => 'onUserCreate',
            Event\User\DeletedEvent::class          => 'onUserDelete',
            Event\User\UpdatedEvent::class          => 'onUserUpdate',
        ];
    }
}
