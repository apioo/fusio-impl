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

namespace Fusio\Impl\Service\A2A;

use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\Agent\Sender;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Record\RecordInterface;
use stdClass;

/**
 * SendMessage
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class SendMessage
{
    public function __construct(
        private Sender $sender,
        private Table\Agent $agentTable,
        private FrameworkConfig $frameworkConfig,

    ) {
    }

    public function invoke(RecordInterface $arguments, Context $context): mixed
    {
        $skillId = $arguments->get('skillId');
        if (empty($skillId)) {
            throw new InvalidRequestException('Provided no skill id');
        }

        $agent = $this->agentTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, $skillId);
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw new InvalidRequestException('Provided skill id does not exist');
        }

        $message = $arguments->get('message');
        if (!$message instanceof stdClass) {
            throw new InvalidRequestException('Message must be an object');
        }

        $contextId = $arguments->get('contextId');
        if (!is_string($contextId)) {
            $contextId = null;
        }

        $parts = $message->parts ?? null;
        if (!is_array($parts)) {
            throw new InvalidRequestException('Provided no parts');
        }

        $input = new Model\Agent\Input();
        $input->setPreviousId($contextId);
        $input->setItem($this->buildItem($parts));

        $output = $this->sender->send($agent->getId(), $input, $context);

        return [
            'role' => 'agent',
            'parts' => $this->buildParts($output),
            'contextId' => $output->getId(),
        ];
    }

    /**
     * @throws InvalidRequestException
     */
    private function buildItem(array $parts): Model\Agent\Item
    {
        if (count($parts) === 1) {
            $part = array_first($parts);
            if (!$part instanceof stdClass) {
                throw new InvalidRequestException('Provided an invalid part');
            }

            return $this->buildItemFromPart($part);
        } elseif (count($parts) > 1) {
            $items = [];
            foreach ($parts as $part) {
                if (!$part instanceof stdClass) {
                    throw new InvalidRequestException('Provided an invalid part');
                }

                $items[] = $this->buildItemFromPart($part);
            }

            $choice = new Model\Agent\ItemChoice();
            $choice->setItems($items);
            return $choice;
        } else {
            throw new InvalidRequestException('No parts provided');
        }
    }

    /**
     * @throws InvalidRequestException
     */
    private function buildItemFromPart(stdClass $part): Model\Agent\Item
    {
        $kind = $part->kind ?? null;
        if ($kind === 'text') {
            $content = $part->text ?? null;

            $item = new Model\Agent\ItemText();
            $item->setContent($content);
            return $item;
        } elseif ($kind === 'data') {
            $payload = $part->data ?? null;

            $item = new Model\Agent\ItemObject();
            $item->setPayload($payload);
            return $item;
        } else {
            throw new InvalidRequestException('Provided an not supported kind');
        }
    }

    private function buildParts(Model\Agent\Output $output): array
    {
        $parts = [];
        $item = $output->getItem();

        if ($item instanceof Model\Agent\ItemText) {
            $parts[] = [
                'kind' => 'text',
                'text' => $item->getContent(),
            ];
        } elseif ($item instanceof Model\Agent\ItemObject) {
            $parts[] = [
                'kind' => 'data',
                'data' => $item->getPayload(),
            ];
        }

        return $parts;
    }
}
