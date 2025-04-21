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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Form\CreatedEvent;
use Fusio\Impl\Event\Form\DeletedEvent;
use Fusio\Impl\Event\Form\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\FormCreate;
use Fusio\Model\Backend\FormUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Form
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Form
{
    public function __construct(
        private Table\Form $formTable,
        private Form\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(FormCreate $form, UserContext $context): int
    {
        $this->validator->assert($form, $context->getTenantId());

        try {
            $this->formTable->beginTransaction();

            $row = new Table\Generated\FormRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Form::STATUS_ACTIVE);
            $row->setName($form->getName() ?? throw new StatusCode\BadRequestException('Provided no name'));
            $row->setOperationId($form->getOperationId() ?? throw new StatusCode\BadRequestException('Provided no operation id'));
            $row->setUiSchema(Parser::encode($form->getUiSchema()));
            $row->setMetadata($form->getMetadata() !== null ? Parser::encode($form->getMetadata()) : null);
            $this->formTable->create($row);

            $formId = $this->formTable->getLastInsertId();
            $form->setId($formId);

            $this->formTable->commit();
        } catch (\Throwable $e) {
            $this->formTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($form, $context));

        return $formId;
    }

    public function update(string $formId, FormUpdate $form, UserContext $context): int
    {
        $existing = $this->formTable->findOneByIdentifier($context->getTenantId(), $formId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find form');
        }

        if ($existing->getStatus() == Table\Form::STATUS_DELETED) {
            throw new StatusCode\GoneException('Form was deleted');
        }

        $this->validator->assert($form, $context->getTenantId(), $existing);

        try {
            $this->formTable->beginTransaction();

            $existing->setName($form->getName() ?? $existing->getName());
            $existing->setOperationId($form->getOperationId() ?? $existing->getOperationId());
            $existing->setUiSchema($form->getUiSchema() !== null ? Parser::encode($form->getUiSchema()) : $existing->getUiSchema());
            $existing->setMetadata($form->getMetadata() !== null ? Parser::encode($form->getMetadata()) : $existing->getMetadata());
            $this->formTable->update($existing);

            $this->formTable->commit();
        } catch (\Throwable $e) {
            $this->formTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($form, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $formId, UserContext $context): int
    {
        $existing = $this->formTable->findOneByIdentifier($context->getTenantId(), $formId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find form');
        }

        if ($existing->getStatus() == Table\Form::STATUS_DELETED) {
            throw new StatusCode\GoneException('Form was deleted');
        }

        try {
            $this->formTable->beginTransaction();

            $existing->setStatus(Table\Form::STATUS_DELETED);
            $this->formTable->update($existing);

            $this->formTable->commit();
        } catch (\Throwable $e) {
            $this->formTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
