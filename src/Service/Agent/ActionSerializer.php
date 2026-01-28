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

namespace Fusio\Impl\Service\Agent;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\ActionRow;
use PSX\Http\Exception\BadRequestException;
use PSX\Json\Parser;

/**
 * ActionSerializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ActionSerializer
{
    public function __construct(private Table\Action $actionTable)
    {
    }

    public function serialize(int $id, ContextInterface $context): string
    {
        $row = $this->actionTable->findOneByTenantAndId($context->getTenantId(), $context->getUser()->getCategoryId(), $id);
        if (!$row instanceof Table\Generated\ActionRow) {
            throw new BadRequestException('Provided an invalid action id: ' . $id);
        }

        $output = 'Action ' . $row->getName() . ':' . "\n";
        $output.= '--' . "\n";
        $output.= $this->buildCode($row) . "\n";
        return $output;
    }

    private function buildCode(ActionRow $row): string
    {
        $config = Parser::decode($row->getConfig());

        if (str_starts_with($row->getClass(), 'Fusio.Adapter.Worker.Action.Worker')) {
            return $config->code ?? '';
        }

        return '';
    }
}
