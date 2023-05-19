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

namespace Fusio\Impl\Authorization\GrantType;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\OAuth2\Credentials;
use PSX\Framework\OAuth2\GrantType\RefreshTokenAbstract;
use PSX\OAuth2\Exception\ServerErrorException;
use PSX\OAuth2\Grant;
use PSX\Sql\Condition;

/**
 * RefreshToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RefreshToken extends RefreshTokenAbstract
{
    private Service\App\Token $appTokenService;
    private Table\App $appTable;
    private string $expireApp;
    private string $expireRefresh;

    public function __construct(Service\App\Token $appTokenService, Table\App $appTable, ConfigInterface $config)
    {
        $this->appTokenService = $appTokenService;
        $this->appTable        = $appTable;
        $this->expireApp       = $config->get('fusio_expire_token');
        $this->expireRefresh   = $config->get('fusio_expire_refresh') ?? 'P3D';
    }

    protected function generate(Credentials $credentials, Grant\RefreshToken $grant)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_KEY, $credentials->getClientId());
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_SECRET, $credentials->getClientSecret());
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
        if (empty($app)) {
            throw new ServerErrorException('Unknown credentials');
        }

        // refresh access token
        return $this->appTokenService->refreshAccessToken(
            $app->getId(),
            $grant->getRefreshToken(),
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            new \DateInterval($this->expireApp),
            new \DateInterval($this->expireRefresh)
        );
    }
}
