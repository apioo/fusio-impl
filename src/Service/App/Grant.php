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

namespace Fusio\Impl\Service\App;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Grant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Grant
{
    /**
     * @var \Fusio\Impl\Table\App
     */
    private $appTable;

    /**
     * @var \Fusio\Impl\Table\User\Grant
     */
    private $userGrantTable;

    /**
     * @var \Fusio\Impl\Table\App\Token
     */
    private $appTokenTable;

    /**
     * @param \Fusio\Impl\Table\App $appTable
     * @param \Fusio\Impl\Table\User\Grant $userGrantTable
     * @param \Fusio\Impl\Table\App\Token $appTokenTable
     */
    public function __construct(Table\App $appTable, Table\User\Grant $userGrantTable, Table\App\Token $appTokenTable)
    {
        $this->appTable       = $appTable;
        $this->userGrantTable = $userGrantTable;
        $this->appTokenTable  = $appTokenTable;
    }

    public function delete($grantId, UserContext $context)
    {
        $userId = $context->getUserId();
        $grant  = $this->userGrantTable->get($grantId);

        if (empty($grant)) {
            throw new StatusCode\NotFoundException('Could not find grant');
        }

        if ($grant['user_id'] != $userId) {
            throw new StatusCode\BadRequestException('Invalid grant id');
        }

        try {
            $this->userGrantTable->beginTransaction();

            $this->userGrantTable->delete($grant);

            // delete tokens
            $this->appTokenTable->removeAllTokensFromAppAndUser($grant['app_id'], $grant['user_id']);

            $this->userGrantTable->commit();
        } catch (\Throwable $e) {
            $this->userGrantTable->rollBack();

            throw $e;
        }
    }
}
