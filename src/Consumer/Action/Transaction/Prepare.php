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

namespace Fusio\Impl\Consumer\Action\Transaction;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Consumer\Model\App_Create;
use Fusio\Impl\Consumer\Model\Transaction_Prepare_Request;
use Fusio\Impl\Service\Transaction;
use PSX\Http\Exception as StatusCode;

/**
 * Prepare
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Prepare extends ActionAbstract
{
    /**
     * @var Transaction
     */
    private $transactionService;

    public function __construct(Transaction $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $body = $request->getPayload();

        assert($body instanceof Transaction_Prepare_Request);

        $approvalUrl = $this->transactionService->prepare(
            $request->get('provider'),
            $body,
            UserContext::newActionContext($context)
        );

        return [
            'approvalUrl' => $approvalUrl,
        ];
    }
}
