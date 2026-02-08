<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Action\Commit;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Http\Exception\NotFoundException;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GetAll implements ActionInterface
{
    public function __construct(private Table\Action $actionTable, private View\Action\Commit $view)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $action = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getUser()->getCategoryId(), $request->get('action_id'));
        if (!$action instanceof Table\Generated\ActionRow) {
            throw new NotFoundException('Provided an invalid action id');
        }

        return $this->view->getCollection(
            $action->getId(),
            QueryFilter::from($request),
            $context
        );
    }
}
