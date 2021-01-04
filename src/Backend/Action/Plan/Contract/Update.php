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

namespace Fusio\Impl\Backend\Action\Plan\Contract;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Plan_Contract_Update;
use Fusio\Model\Backend\Plan_Update;
use Fusio\Model\Backend\Route_Update;
use Fusio\Model\Backend\Schema_Update;
use Fusio\Impl\Service\Plan;
use Fusio\Impl\Service\Route;
use Fusio\Impl\Service\Schema;

/**
 * Update
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Update extends ActionAbstract
{
    /**
     * @var Plan\Contract
     */
    private $contractService;

    public function __construct(Plan\Contract $contractService)
    {
        $this->contractService = $contractService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $body = $request->getPayload();

        assert($body instanceof Plan_Contract_Update);

        $this->contractService->update(
            (int) $request->get('contract_id'),
            $body,
            UserContext::newActionContext($context)
        );

        return [
            'success' => true,
            'message' => 'Contract successful updated',
        ];
    }
}
