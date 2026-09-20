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

namespace Fusio\Impl\Backend\Action\Agent\Introduction;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Table;
use PSX\Json\Parser;
use stdClass;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Action implements ActionInterface
{
    public function __construct(private Table\Action $actionTable)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): array
    {
        $refId = (int) $request->get('refId');
        if (empty($refId)) {
            return [];
        }

        $row = $this->actionTable->findOneByTenantAndId($context->getTenantId(), $context->getUser()->getCategoryId(), $refId);
        if (!$row instanceof Table\Generated\ActionRow) {
            return [];
        }

        $code = $this->getCodeFromConfig($row);
        if (empty($code)) {
            return [];
        }

        return [
            'name' => $row->getName(),
            'code' => $code,
        ];
    }

    private function getCodeFromConfig(Table\Generated\ActionRow $row): ?string
    {
        $config = $row->getConfig();
        if (empty($config)) {
            return null;
        }

        $data = Parser::decode($config);
        if (!$data instanceof stdClass) {
            return $data;
        }

        $code = $data->code ?? null;
        if (!is_string($code)) {
            return null;
        }

        return $code;
    }
}
