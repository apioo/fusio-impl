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

namespace Fusio\Impl\Service\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Grant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Grant
{
    private Table\User\Grant $userGrantTable;
    private Table\Token $tokenTable;

    public function __construct(Table\User\Grant $userGrantTable, Table\Token $tokenTable)
    {
        $this->userGrantTable = $userGrantTable;
        $this->tokenTable = $tokenTable;
    }

    public function delete(int $grantId, UserContext $context): int
    {
        $userId = $context->getUserId();
        $grant  = $this->userGrantTable->find($grantId);

        if (empty($grant)) {
            throw new StatusCode\NotFoundException('Could not find grant');
        }

        if ($grant->getUserId() != $userId) {
            throw new StatusCode\BadRequestException('Invalid grant id');
        }

        try {
            $this->userGrantTable->beginTransaction();

            $this->userGrantTable->delete($grant);

            // delete tokens
            $this->tokenTable->removeAllTokensFromAppAndUser($context->getTenantId(), $grant->getAppId(), $grant->getUserId());

            $this->userGrantTable->commit();
        } catch (\Throwable $e) {
            $this->userGrantTable->rollBack();

            throw $e;
        }

        return $grantId;
    }
}
