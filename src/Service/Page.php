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
use Fusio\Impl\Event\Page\CreatedEvent;
use Fusio\Impl\Event\Page\DeletedEvent;
use Fusio\Impl\Event\Page\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\PageCreate;
use Fusio\Model\Backend\PageUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Page
{
    private Table\Page $pageTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Page $pageTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->pageTable       = $pageTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(PageCreate $page, UserContext $context): int
    {
        $title = $page->getTitle();
        if (empty($title)) {
            throw new StatusCode\BadRequestException('Title not provided');
        }

        $slug = $this->createSlug($title);

        // check whether page exists
        if ($this->exists($slug)) {
            throw new StatusCode\BadRequestException('Page already exists');
        }

        $this->assertStatus($page);

        // create page
        try {
            $this->pageTable->beginTransaction();

            $row = new Table\Generated\PageRow();
            $row->setStatus($page->getStatus());
            $row->setTitle($title);
            $row->setSlug($slug);
            $row->setContent($page->getContent());
            $row->setMetadata($page->getMetadata() !== null ? json_encode($page->getMetadata()) : null);
            $row->setDate(LocalDateTime::now());
            $this->pageTable->create($row);

            $pageId = $this->pageTable->getLastInsertId();
            $page->setId($pageId);

            $this->pageTable->commit();
        } catch (\Throwable $e) {
            $this->pageTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($page, $context));

        return $pageId;
    }

    public function update(string $pageId, PageUpdate $page, UserContext $context): int
    {
        $existing = $this->pageTable->findOneByIdentifier($pageId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find page');
        }

        if ($existing->getStatus() == Table\Page::STATUS_DELETED) {
            throw new StatusCode\GoneException('Page was deleted');
        }

        $title = $page->getTitle();
        if (empty($title)) {
            throw new StatusCode\BadRequestException('Title not provided');
        }

        $slug = $this->createSlug($title);

        $this->assertStatus($page);

        // update action
        $existing->setStatus($page->getStatus());
        $existing->setTitle($title);
        $existing->setSlug($slug);
        $existing->setContent($page->getContent());
        $existing->setMetadata($page->getMetadata() !== null ? json_encode($page->getMetadata()) : null);
        $this->pageTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($page, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $pageId, UserContext $context): int
    {
        $existing = $this->pageTable->findOneByIdentifier($pageId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find page');
        }

        if ($existing->getStatus() == Table\Page::STATUS_DELETED) {
            throw new StatusCode\GoneException('Page was deleted');
        }

        $existing->setStatus(Table\Page::STATUS_DELETED);
        $this->pageTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function exists(string $slug): int|false
    {
        $condition = Condition::withAnd();
        $condition->in(Table\Generated\PageTable::COLUMN_STATUS, [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE]);
        $condition->equals(Table\Generated\PageTable::COLUMN_SLUG, $slug);

        $page = $this->pageTable->findOneBy($condition);

        if ($page instanceof Table\Generated\PageRow) {
            return $page->getId();
        } else {
            return false;
        }
    }

    /**
     * Generates a slug from the title
     *
     * @see https://haensel.pro/php/php-function-create-slugs-from-string
     * @param string $title
     * @return string
     */
    private function createSlug(string $title): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $slug);
        $slug = strtolower(trim($slug, '-'));
        $slug = preg_replace('/[\/_|+ -]+/', '-', $slug);
        return $slug;
    }

    private function assertStatus(\Fusio\Model\Backend\Page $page): void
    {
        if (!in_array($page->getStatus(), [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE])) {
            throw new StatusCode\GoneException('Page status must be either 1 or 2');
        }
    }
}
