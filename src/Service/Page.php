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
use Fusio\Impl\Event\Page\CreatedEvent;
use Fusio\Impl\Event\Page\DeletedEvent;
use Fusio\Impl\Event\Page\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Page_Create;
use Fusio\Model\Backend\Page_Update;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Page
{
    /**
     * @var \Fusio\Impl\Table\Page
     */
    private $pageTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Page $pageTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Page $pageTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->pageTable       = $pageTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Page_Create $page, UserContext $context)
    {
        $slug = $this->createSlug($page->getTitle());

        // check whether page exists
        if ($this->exists($slug)) {
            throw new StatusCode\BadRequestException('Page already exists');
        }

        $this->assertStatus($page);

        // create page
        $record = [
            'status'  => $page->getStatus(),
            'title'   => $page->getTitle(),
            'slug'    => $slug,
            'content' => $page->getContent(),
            'date'    => new \DateTime(),
        ];

        $this->pageTable->create($record);

        $pageId = $this->pageTable->getLastInsertId();
        $page->setId($pageId);

        $this->eventDispatcher->dispatch(new CreatedEvent($page, $context));

        return $pageId;
    }

    public function update(int $pageId, Page_Update $page, UserContext $context)
    {
        $existing = $this->pageTable->get($pageId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find page');
        }

        if ($existing['status'] == Table\Page::STATUS_DELETED) {
            throw new StatusCode\GoneException('Page was deleted');
        }

        $this->assertStatus($page);

        $slug = $this->createSlug($page->getTitle());

        // update action
        $record = [
            'id'      => $existing['id'],
            'status'  => $page->getStatus(),
            'title'   => $page->getTitle(),
            'slug'    => $slug,
            'content' => $page->getContent(),
        ];

        $this->pageTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($page, $existing, $context));
    }

    public function delete(int $pageId, UserContext $context)
    {
        $existing = $this->pageTable->get($pageId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find page');
        }

        if ($existing['status'] == Table\Page::STATUS_DELETED) {
            throw new StatusCode\GoneException('Page was deleted');
        }

        $this->pageTable->update([
            'id'     => $existing['id'],
            'status' => Table\Page::STATUS_DELETED,
        ]);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function exists(string $slug)
    {
        $condition  = new Condition();
        $condition->in('status', [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE]);
        $condition->equals('slug', $slug);

        $page = $this->pageTable->getOneBy($condition);

        if (!empty($page)) {
            return $page['id'];
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

    private function assertStatus(\Fusio\Model\Backend\Page $page)
    {
        if (!in_array($page->getStatus(), [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE])) {
            throw new StatusCode\GoneException('Page status must be either 1 or 2');
        }
    }
}
