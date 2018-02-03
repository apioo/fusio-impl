<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\User;

use Firebase\JWT\JWT;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;

/**
 * Activate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Activate
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Service\User $userService, Config $config)
    {
        $this->userService = $userService;
        $this->config      = $config;
    }

    public function activate($token)
    {
        $payload = JWT::decode($token, $this->config->get('fusio_project_key'), ['HS256']);
        $userId  = isset($payload->sub) ? $payload->sub : null;
        $expires = isset($payload->exp) ? $payload->exp : null;

        if (time() < $expires) {
            $this->userService->changeStatus($userId, Table\User::STATUS_CONSUMER, UserContext::newAnonymousContext());
        } else {
            throw new StatusCode\BadRequestException('Token is expired');
        }
    }
}
