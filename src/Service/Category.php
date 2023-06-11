<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        $existing->setStatus(Table\Category::STATUS_DELETED);
        $this->categoryTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
