<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Service;

use Fusio\Engine\Action\LifecycleInterface;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\Factory;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\Inflection\ClassShape;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Action\CreatedEvent;
use Fusio\Impl\Event\Action\DeletedEvent;
use Fusio\Impl\Event\Action\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Marketplace\MarketplaceMessageException;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use JsonException;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use Sdkgen\Client\Exception\ClientException;
use Throwable;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Action
{
    public function __construct(
        private Action\Committer $actionCommitter,
        private Action\Validator $validator,
        private Table\Action $actionTable,
        private Factory\ActionInterface $actionFactory,
        private Marketplace\Action\Repository $marketplaceRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function create(ActionCreate $action, UserContext $context): int
    {
        $this->validator->assert($action, $context->getTenantId());

        $name = $action->getName() ?? throw new StatusCode\BadRequestException('Provided no action name');
        $class = $action->getClass() ?? throw new StatusCode\BadRequestException('Provided no action class');

        // create action
        try {
            $this->actionTable->beginTransaction();

            // resolve class
            $resolvedConfig = [];
            $shape = ClassShape::fromString($class);
            $class = $this->resolveClass($class, $shape, $context, $resolvedConfig);

            $config = $action->getConfig()?->getAll() ?? [];
            if (count($config) === 0) {
                $config = $resolvedConfig;
            }

            $handlerConfig = $config;
            $handler = $this->newAction($class, $context, $handlerConfig);

            $row = new Table\Generated\ActionRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Action::STATUS_ACTIVE);
            $row->setName($name);
            $row->setClass($class);
            $row->setAsync($action->getAsync() ?? false);
            $row->setConfig(self::serializeConfig($config));
            $row->setMetadata($action->getMetadata() !== null ? Parser::encode($action->getMetadata()) : null);
            $row->setDate(LocalDateTime::now());
            $this->actionTable->create($row);

            $actionId = $this->actionTable->getLastInsertId();
            $action->setId($actionId);

            $hash = $this->actionCommitter->commit($actionId, $row->getConfig(), $context);

            // call lifecycle
            if ($handler instanceof LifecycleInterface) {
                /** @phpstan-ignore arguments.count */
                $handler->onCreate($name, new Parameters($handlerConfig), $hash);
            }

            $this->actionTable->commit();
        } catch (Throwable $e) {
            $this->actionTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($action, $context));

        return $actionId;
    }

    public function update(string $actionId, ActionUpdate $action, UserContext $context): int
    {
        $existing = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $this->validator->assert($action, $context->getTenantId(), $existing);

        $name = $action->getName() ?? $existing->getName();
        $class = $existing->getClass();

        $config = $action->getConfig()?->getAll() ?? self::unserializeConfig($existing->getConfig());

        $handlerConfig = $config ?? [];
        $handler = $this->newAction($class, $context, $handlerConfig);

        // update action
        try {
            $this->actionTable->beginTransaction();

            $existing->setName($name);
            $existing->setAsync($action->getAsync() ?? $existing->getAsync());
            $existing->setConfig(self::serializeConfig($config));
            $existing->setMetadata($action->getMetadata() !== null ? Parser::encode($action->getMetadata()) : $existing->getMetadata());
            $existing->setDate(LocalDateTime::now());
            $this->actionTable->update($existing);

            $hash = $this->actionCommitter->commit($existing->getId(), $existing->getConfig(), $context);

            // call lifecycle
            if ($handler instanceof LifecycleInterface) {
                /** @phpstan-ignore arguments.count */
                $handler->onUpdate($name, new Parameters($handlerConfig), $hash);
            }

            $this->actionTable->commit();
        } catch (Throwable $e) {
            $this->actionTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($action, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $actionId, UserContext $context): int
    {
        $existing = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $class = $existing->getClass();

        $config = self::unserializeConfig($existing->getConfig());

        $handlerConfig = $config ?? [];
        $handler = $this->newAction($class, $context, $handlerConfig);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onDelete($existing->getName(), new Parameters($config));
        }

        $existing->setStatus(Table\Action::STATUS_DELETED);
        $this->actionTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    /**
     * Checks whether the provided class is resolvable and returns an action instance
     */
    private function newAction(string $class, UserContext $context, array &$config): ActionInterface
    {
        $shape = ClassShape::fromString($class);
        if ($shape === ClassShape::PHP_CLASS) {
            try {
                return $this->actionFactory->factory($class);
            } catch (FactoryResolveException $e) {
                throw new StatusCode\BadRequestException($e->getMessage());
            }
        } elseif ($shape === ClassShape::ENTITY_NAME) {
            $row = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $class);
            if (!$row instanceof Table\Generated\ActionRow) {
                throw new StatusCode\BadRequestException('Could not resolve action class');
            }

            $actionConfig = self::unserializeConfig($row->getConfig());
            if ($actionConfig !== null) {
                $config = $actionConfig;
            }

            return $this->newAction($row->getClass(), $context, $config);
        } else {
            throw new StatusCode\BadRequestException('Provided an invalid action class shape');
        }
    }

    private function resolveClass(string $class, ClassShape $shape, UserContext $context, array &$config): string
    {
        return match ($shape) {
            ClassShape::PHP_CLASS => ClassName::serialize($class),
            ClassShape::ENTITY_NAME => $this->resolveEntityName($class, $context),
            ClassShape::MARKETPLACE_ENTITY => $this->resolveMarketplaceEntity($class, $context, $config),
        };
    }

    private function resolveEntityName(string $class, UserContext $context): string
    {
        $row = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $class);
        if (!$row instanceof Table\Generated\ActionRow) {
            throw new StatusCode\BadRequestException('Could not resolve action class');
        }

        return $row->getName();
    }

    private function resolveMarketplaceEntity(string $class, UserContext $context, array &$config): string
    {
        $parts = explode('/', $class, 2);
        $user = $parts[0] ?? null;
        $name = $parts[1] ?? null;

        if (empty($user) || empty($name)) {
            throw new StatusCode\BadRequestException('Provided an invalid marketplace action name');
        }

        $actionName = $user . '-' . $name;

        $row = $this->actionTable->findOneByTenantAndName($context->getTenantId(), $context->getCategoryId(), $actionName);
        if ($row instanceof Table\Generated\ActionRow) {
            return $row->getName();
        }

        try {
            $action = $this->marketplaceRepository->install($user, $name);
        } catch (MarketplaceMessageException $e) {
            $message = $e->getPayload()->getMessage();

            throw new StatusCode\InternalServerErrorException('Could not fetch action from marketplace: ' . $message, previous: $e);
        } catch (ClientException $e) {
            throw new StatusCode\InternalServerErrorException('Could not fetch action from marketplace: ' . $e->getMessage(), previous: $e);
        }

        $form = $action->getForm() ?? [];
        if (count($form) > 0) {
            // we have an action which is configurable we copy the action from the marketplace
            $create = new ActionCreate();
            $create->setName($actionName);
            $create->setClass($action->getClass());
            $create->setConfig(ActionConfig::from($action->getConfig() ?? []));
            $this->create($create, $context);

            return $actionName;
        } else {
            $actionConfig = $action->getConfig()?->getAll();
            if (is_array($actionConfig)) {
                $config = $actionConfig;
            }

            return $action->getClass();
        }
    }

    /**
     * @param array<string, mixed>|null $config
     * @throws JsonException
     */
    public static function serializeConfig(?array $config = null): ?string
    {
        if (empty($config)) {
            return null;
        }

        return Parser::encode($config);
    }

    /**
     * @return array<string, mixed>|null
     * @throws JsonException
     */
    public static function unserializeConfig(?string $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        return Parser::decodeAsArray($data);
    }
}
