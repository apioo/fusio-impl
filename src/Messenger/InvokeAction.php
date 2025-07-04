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

namespace Fusio\Impl\Messenger;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\RequestInterface;

/**
 * InvokeAction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InvokeAction
{
    private string|int $actionId;
    private RequestInterface $request;
    private ContextInterface $context;

    public function __construct(string|int $actionId, RequestInterface $request, ContextInterface $context)
    {
        $this->actionId = $actionId;
        $this->request = $request;
        $this->context = $context;
    }

    public function getActionId(): int|string
    {
        return $this->actionId;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}
