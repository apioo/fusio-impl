<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

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
    protected $appTable;

    /**
     * @var \Fusio\Impl\Table\User\Grant
     */
    protected $userGrantTable;

    /**
     * @var \Fusio\Impl\Table\App\Token
     */
    protected $appTokenTable;

    public function __construct(Table\App $appTable, Table\User\Grant $userGrantTable, Table\App\Token $appTokenTable)
    {
        $this->appTable       = $appTable;
        $this->userGrantTable = $userGrantTable;
        $this->appTokenTable  = $appTokenTable;
    }

    public function getAll($userId)
    {
        return $this->appTable->getAuthorizedApps($userId);
    }

    public function delete($userId, $grantId)
    {
        $grant = $this->userGrantTable->get($grantId);

        if (!empty($grant)) {
            if ($grant['userId'] == $userId) {
                try {
                    $this->userGrantTable->beginTransaction();

                    $this->userGrantTable->delete($grant);

                    // delete tokens
                    $this->appTokenTable->removeAllTokensFromAppAndUser($grant['appId'], $grant['userId']);

                    $this->userGrantTable->commit();
                } catch (\Exception $e) {
                    $this->userGrantTable->rollBack();

                    throw $e;
                }
            } else {
                throw new StatusCode\BadRequestException('Invalid grant id');
            }
        } else {
            throw new StatusCode\NotFoundException('Could not find grant');
        }
    }
}
