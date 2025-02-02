<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\System\Action\Meta;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * GetOAuth2Configuration
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetOAuth2Configuration implements ActionInterface
{
    private Service\System\FrameworkConfig $frameworkConfig;
    private Table\Scope $scopeTable;

    public function __construct(Service\System\FrameworkConfig $frameworkConfig, Table\Scope $scopeTable)
    {
        $this->frameworkConfig = $frameworkConfig;
        $this->scopeTable = $scopeTable;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return [
            'issuer' => $this->frameworkConfig->getUrl(),
            'token_endpoint' => $this->frameworkConfig->getDispatchUrl('authorization', 'token'),
            'token_endpoint_auth_methods_supported' => ['client_secret_basic'],
            'userinfo_endpoint' => $this->frameworkConfig->getDispatchUrl('authorization', 'whoami'),
            'scopes_supported' => $this->getScopes(),
            'claims_supported' => ['iss', 'sub', 'iat', 'exp', 'name'],
        ];
    }

    private function getScopes(): array
    {
        $condition = Condition::withAnd();
        $condition->equals('category_id', 1);
        $categories = $this->scopeTable->findAll($condition, 0, 1024, Table\Generated\ScopeColumn::NAME, OrderBy::ASC);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row->getName();
        }

        return $result;
    }

}
