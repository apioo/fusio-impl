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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Category\CreatedEvent;
use Fusio\Impl\Event\Category\DeletedEvent;
use Fusio\Impl\Event\Category\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\CategoryCreate;
use Fusio\Model\Backend\CategoryUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Category
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Category
{
    private Table\Category $categoryTable;
    private Category\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Category $categoryTable, Category\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->categoryTable = $categoryTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(CategoryCreate $category, UserContext $context): int
    {
        $this->validator->assert($category);

        try {
            $this->categoryTable->beginTransaction();

            // create category
            $row = new Table\Generated\CategoryRow();
            $row->setStatus(Table\Rate::STATUS_ACTIVE);
            $row->setName($category->getName());
            $this->categoryTable->create($row);

            $categoryId = $this->categoryTable->getLastInsertId();
            $category->setId($categoryId);

            $this->categoryTable->commit();
        } catch (\Throwable $e) {
            $this->categoryTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($category, $context));

        return $categoryId;
    }

    public function update(string $categoryId, CategoryUpdate $category, UserContext $context): int
    {
        $existing = $this->categoryTable->findOneByIdentifier($categoryId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find category');
        }

        if ($existing->getStatus() == Table\Category::STATUS_DELETED) {
            throw new StatusCode\GoneException('Category was deleted');
        }

        $this->validator->assert($category, $existing);

        try {
            $this->categoryTable->beginTransaction();

            // update category
            $existing->setName($category->getName() ?? $existing->getName());
            $this->categoryTable->update($existing);

            $this->categoryTable->commit();
        } catch (\Throwable $e) {
            $this->categoryTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($category, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $categoryId, UserContext $context): int
    {
        $existing = $this->categoryTable->findOneByIdentifier($categoryId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find category');
        }

        if ($existing->getStatus() == Table\Category::STATUS_DELETED) {
            throw new StatusCode\GoneException('Category was deleted');
        }

        $existing->setStatus(Table\Category::STATUS_DELETED);
        $this->categoryTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
