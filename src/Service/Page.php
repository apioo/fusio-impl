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
use Fusio\Impl\Event\Page\CreatedEvent;
use Fusio\Impl\Event\Page\DeletedEvent;
use Fusio\Impl\Event\Page\UpdatedEvent;
use Fusio\Impl\Service\Page\SlugBuilder;
use Fusio\Impl\Table;
use Fusio\Model\Backend\PageCreate;
use Fusio\Model\Backend\PageUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Page
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Page
{
    private Table\Page $pageTable;
    private Page\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Page $pageTable, Page\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->pageTable = $pageTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(PageCreate $page, UserContext $context): int
    {
        $this->validator->assert($page, $context->getTenantId());

        $title = $page->getTitle();
        $slug = SlugBuilder::build($title);

        // create page
        try {
            $this->pageTable->beginTransaction();

            $row = new Table\Generated\PageRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus($page->getStatus());
            $row->setTitle($title);
            $row->setSlug($slug);
            $row->setContent($page->getContent());
            $row->setMetadata($page->getMetadata() !== null ? Parser::encode($page->getMetadata()) : null);
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
        $existing = $this->pageTable->findOneByIdentifier($context->getTenantId(), $pageId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find page');
        }

        if ($existing->getStatus() == Table\Page::STATUS_DELETED) {
            throw new StatusCode\GoneException('Page was deleted');
        }

        $this->validator->assert($page, $context->getTenantId(), $existing);

        $title = $page->getTitle();
        $slug = $title !== null ? SlugBuilder::build($title) : null;

        // update action
        $existing->setStatus($page->getStatus() ?? $existing->getStatus());
        $existing->setTitle($title ?? $existing->getTitle());
        $existing->setSlug($slug ?? $existing->getSlug());
        $existing->setContent($page->getContent() ?? $existing->getContent());
        $existing->setMetadata($page->getMetadata() !== null ? Parser::encode($page->getMetadata()) : $existing->getMetadata());
        $this->pageTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($page, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $pageId, UserContext $context): int
    {
        $existing = $this->pageTable->findOneByIdentifier($context->getTenantId(), $pageId);
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
}
