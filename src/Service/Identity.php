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
use Fusio\Impl\Event\Identity\CreatedEvent;
use Fusio\Impl\Event\Identity\DeletedEvent;
use Fusio\Impl\Event\Identity\UpdatedEvent;
use Fusio\Impl\Provider\UserProvider;
use Fusio\Impl\Service\Identity\Configuration;
use Fusio\Model\Backend;
use PSX\Http\Exception as StatusCode;

/**
 * Identity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Identity
{
    private Table\Identity $identityTable;
    private Category\Validator $validator;
    private UserProvider $userProvider;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Identity $identityTable, Identity\Validator $validator, UserProvider $userProvider, EventDispatcherInterface $eventDispatcher)
    {
        $this->identityTable = $identityTable;
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(IdentityCreate $identity, UserContext $context): int
    {
        $this->validator->assert($identity);

        try {
            $this->identityTable->beginTransaction();

            // create category
            $row = new Table\Generated\IdentityRow();
            $row->setStatus(Table\Identity::STATUS_ACTIVE);
            $row->setName($identity->getName());
            $this->identityTable->create($row);

            $identityId = $this->identityTable->getLastInsertId();
            $identity->setId($identityId);

            $this->identityTable->commit();
        } catch (\Throwable $e) {
            $this->identityTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($identity, $context));

        return $identityId;
    }

    public function update(string $identityId, IdentityUpdate $identity, UserContext $context): int
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $this->validator->assert($identity, $existing);

        try {
            $this->identityTable->beginTransaction();

            // update category
            $existing->setName($identity->getName() ?? $existing->getName());
            $this->identityTable->update($existing);

            $this->identityTable->commit();
        } catch (\Throwable $e) {
            $this->identityTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($identity, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $identityId, UserContext $context): int
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $existing->setStatus(Table\Identity::STATUS_DELETED);
        $this->identityTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function getConfiguration(string $identityId): Configuration
    {
        $existing = $this->identityTable->findOneByIdentifier($identityId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find identity');
        }

        if ($existing->getStatus() == Table\Identity::STATUS_DELETED) {
            throw new StatusCode\GoneException('Identity was deleted');
        }

        $provider = $this->userProvider->getInstance($existing->getProvider());

        return new Configuration($provider, $existing);
    }
}
