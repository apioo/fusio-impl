<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Authorization\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\TableManagerInterface;

/**
 * Revoke
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Revoke extends ActionAbstract
{
    /**
     * @var Service\App\Token
     */
    private $appTokenService;

    /**
     * @var Table\App\Token
     */
    private $table;

    public function __construct(Service\App\Token $appTokenService, TableManagerInterface $tableManager)
    {
        $this->appTokenService = $appTokenService;
        $this->table = $tableManager->getTable(Table\App\Token::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        if ($request instanceof HttpRequest) {
            $token = $this->getTokenByHttp($request);
        } else {
            $token = $request->get('token');
        }

        $row = $this->table->getTokenByToken($context->getApp()->getId(), $token);

        // the token must be assigned to the user
        if (!empty($row) && $row['app_id'] == $context->getApp()->getId() && $row['user_id'] == $context->getUser()->getId()) {
            $this->appTokenService->removeToken($row['app_id'], $row['id'], UserContext::newActionContext($context));

            return [
                'success' => true
            ];
        } else {
            throw new StatusCode\BadRequestException('Invalid token');
        }
    }

    private function getTokenByHttp(HttpRequest $request): ?string
    {
        $header = $request->getHeader('Authorization');
        $parts  = explode(' ', $header, 2);
        $type   = isset($parts[0]) ? $parts[0] : null;
        $token  = isset($parts[1]) ? $parts[1] : null;

        if ($type !== 'Bearer') {
            throw new StatusCode\BadRequestException('Invalid token type');
        }

        return $token;
    }
}
