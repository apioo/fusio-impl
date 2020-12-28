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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Category_Create;
use Fusio\Impl\Backend\Model\Category_Update;
use Fusio\Impl\Event\Category\CreatedEvent;
use Fusio\Impl\Event\Category\DeletedEvent;
use Fusio\Impl\Event\Category\UpdatedEvent;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Category
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Category
{
    /**
     * @var \Fusio\Impl\Table\Category
     */
    private $categoryTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Category $categoryTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Category $categoryTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->categoryTable   = $categoryTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Category_Create $category, UserContext $context)
    {
        // check whether rate exists
        if ($this->exists($category->getName())) {
            throw new StatusCode\BadRequestException('Category already exists');
        }

        try {
            $this->categoryTable->beginTransaction();

            // create category
            $record = [
                'status' => Table\Rate::STATUS_ACTIVE,
                'name'   => $category->getName(),
            ];

            $this->categoryTable->create($record);

            // get last insert id
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

    public function update(int $categoryId, Category_Update $category, UserContext $context)
    {
        $existing = $this->categoryTable->get($categoryId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find category');
        }

        if ($existing['status'] == Table\Category::STATUS_DELETED) {
            throw new StatusCode\GoneException('Category was deleted');
        }

        try {
            $this->categoryTable->beginTransaction();

            // update category
            $record = [
                'id'   => $existing['id'],
                'name' => $category->getName(),
            ];

            $this->categoryTable->update($record);

            $this->categoryTable->commit();
        } catch (\Throwable $e) {
            $this->categoryTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($category, $existing, $context));
    }

    public function delete($categoryId, UserContext $context)
    {
        $existing = $this->categoryTable->get($categoryId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find category');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Category::STATUS_DELETED,
        ];

        $this->categoryTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->notEquals('status', Table\Category::STATUS_DELETED);
        $condition->equals('name', $name);

        $category = $this->categoryTable->getOneBy($condition);

        if (!empty($category)) {
            return $category['id'];
        } else {
            return false;
        }
    }
}
