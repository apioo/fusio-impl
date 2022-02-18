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

namespace Fusio\Impl\Service\App;

use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Table;

/**
 * Code
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Code
{
    private Table\App\Code $appCodeTable;

    public function __construct(Table\App\Code $appCodeTable)
    {
        $this->appCodeTable = $appCodeTable;
    }

    public function generateCode($appId, $userId, $redirectUri, array $scopes): string
    {
        $code = TokenGenerator::generateCode();

        $this->appCodeTable->create(new Table\Generated\AppCodeRow([
            Table\Generated\AppCodeTable::COLUMN_APP_ID => $appId,
            Table\Generated\AppCodeTable::COLUMN_USER_ID => $userId,
            Table\Generated\AppCodeTable::COLUMN_CODE => $code,
            Table\Generated\AppCodeTable::COLUMN_REDIRECT_URI => $redirectUri,
            Table\Generated\AppCodeTable::COLUMN_SCOPE => implode(',', $scopes),
            Table\Generated\AppCodeTable::COLUMN_DATE => new \DateTime(),
        ]));

        return $code;
    }
}
