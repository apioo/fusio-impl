<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Table;
use PSX\Framework\Controller\ApiAbstract;
use PSX\Http\Exception as StatusCode;

/**
 * Revoke
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Revoke extends ApiAbstract
{
    use ProtectionTrait;

    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    public function onPost()
    {
        $header = $this->getHeader('Authorization');
        $parts  = explode(' ', $header, 2);
        $type   = isset($parts[0]) ? $parts[0] : null;
        $token  = isset($parts[1]) ? $parts[1] : null;

        if ($type == 'Bearer') {
            $row = $this->tableManager->getTable(Table\App\Token::class)->getTokenByToken($this->appId, $token);

            // the token must be assigned to the user
            if (!empty($row) && $row['appId'] == $this->appId && $row['userId'] == $this->userId) {
                $this->appService->removeToken($row['appId'], $row['id'], $this->userContext);

                $this->setBody([
                    'success' => true
                ]);
            } else {
                throw new StatusCode\BadRequestException('Invalid token');
            }
        } else {
            throw new StatusCode\BadRequestException('Invalid token type');
        }
    }
}
