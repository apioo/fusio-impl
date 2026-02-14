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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Taxonomy\CreatedEvent;
use Fusio\Impl\Event\Taxonomy\DeletedEvent;
use Fusio\Impl\Event\Taxonomy\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\TaxonomyCreate;
use Fusio\Model\Backend\TaxonomyUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;

/**
 * Taxonomy
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Taxonomy
{
    public function __construct(
        private Table\Taxonomy $taxonomyTable,
        private Taxonomy\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(TaxonomyCreate $taxonomy, UserContext $context): int
    {
        $this->validator->assert($taxonomy, $context->getTenantId());

        try {
            $this->taxonomyTable->beginTransaction();

            // create taxonomy
            $row = new Table\Generated\TaxonomyRow();
            $row->setTenantId($context->getTenantId());
            $row->setParentId($taxonomy->getParentId());
            $row->setStatus(Table\Taxonomy::STATUS_ACTIVE);
            $row->setName($taxonomy->getName());
            $row->setInsertDate(LocalDateTime::now());
            $this->taxonomyTable->create($row);

            $taxonomyId = $this->taxonomyTable->getLastInsertId();
            $taxonomy->setId($taxonomyId);

            $this->taxonomyTable->commit();
        } catch (\Throwable $e) {
            $this->taxonomyTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($taxonomy, $context));

        return $taxonomyId;
    }

    public function update(string $taxonomyId, TaxonomyUpdate $taxonomy, UserContext $context): int
    {
        $existing = $this->taxonomyTable->findOneByIdentifier($context->getTenantId(), $taxonomyId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find taxonomy');
        }

        if ($existing->getStatus() == Table\Taxonomy::STATUS_DELETED) {
            throw new StatusCode\GoneException('Taxonomy was deleted');
        }

        $this->validator->assert($taxonomy, $context->getTenantId(), $existing);

        try {
            $this->taxonomyTable->beginTransaction();

            // update taxonomy
            $existing->setParentId($taxonomy->getParentId() ?? $existing->getParentId());
            $existing->setName($taxonomy->getName() ?? $existing->getName());
            $this->taxonomyTable->update($existing);

            $this->taxonomyTable->commit();
        } catch (\Throwable $e) {
            $this->taxonomyTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($taxonomy, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $taxonomyId, UserContext $context): int
    {
        $existing = $this->taxonomyTable->findOneByIdentifier($context->getTenantId(), $taxonomyId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find taxonomy');
        }

        if ($existing->getStatus() == Table\Taxonomy::STATUS_DELETED) {
            throw new StatusCode\GoneException('Taxonomy was deleted');
        }

        $existing->setStatus(Table\Taxonomy::STATUS_DELETED);
        $this->taxonomyTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
