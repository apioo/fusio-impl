<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Account;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Service\User;
use Fusio\Model\Backend\UserUpdate;

/**
 * Update
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Update implements ActionInterface
{
    private User $userService;
    private ContextFactory $contextFactory;

    public function __construct(User $userService, ContextFactory $contextFactory)
    {
        $this->userService = $userService;
        $this->contextFactory = $contextFactory;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof UserUpdate);

        $id = $this->userService->update(
            (string) $context->getUser()->getId(),
            $body,
            $this->contextFactory->newActionContext($context)
        );

        return [
            'success' => true,
            'message' => 'Account successful changed',
            'id' => '' . $id,
        ];
    }
}
