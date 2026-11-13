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

namespace Fusio\Impl\Service\Agent\Serializer;

use Fusio\Model\Agent\Item;
use Fusio\Model\Agent\ItemBinary;
use Fusio\Model\Agent\ItemChoice;
use Fusio\Model\Agent\ItemObject;
use Fusio\Model\Agent\ItemText;
use Fusio\Model\Agent\ItemToolCall;
use Fusio\Model\Agent\ItemToolCallFunction;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Json\Parser;
use Symfony\AI\Platform\Result\BinaryResult;
use Symfony\AI\Platform\Result\ChoiceResult;
use Symfony\AI\Platform\Result\MultiPartResult;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\AI\Platform\Result\ToolCallResult;

/**
 * ResultSerializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ResultSerializer
{
    public function serialize(ResultInterface $result): Item
    {
        if ($result instanceof MultiPartResult) {
            $item = new ItemText();
            $item->setType('text');
            $item->setContent($result->asText());
        } elseif ($result instanceof BinaryResult) {
            $item = new ItemBinary();
            $item->setType('binary');
            $item->setMime($result->getMimeType());
            $item->setData($result->toBase64());
        } elseif ($result instanceof ChoiceResult) {
            $items = [];
            foreach ($result->getContent() as $choice) {
                $items[] = $this->serialize($choice);
            }

            $item = new ItemChoice();
            $item->setType('choice');
            $item->setItems($items);
        } elseif ($result instanceof ObjectResult) {
            $item = new ItemObject();
            $item->setType('object');
            $item->setPayload($result->getContent());
        } elseif ($result instanceof TextResult) {
            $item = new ItemText();
            $item->setType('text');
            $item->setContent($result->getContent());
        } elseif ($result instanceof ToolCallResult) {
            $functions = [];
            foreach ($result->getContent() as $toolCall) {
                $function = new ItemToolCallFunction();
                $function->setName($toolCall->getName());
                $function->setArguments(Parser::encode($toolCall->getArguments()));
                $function->setId($toolCall->getId());
                $functions[] = $function;
            }

            $item = new ItemToolCall();
            $item->setType('tool_call');
            $item->setFunctions($functions);
        } else {
            throw new InternalServerErrorException('Provided an unsupported result type: ' . $result::class);
        }

        return $item;
    }
}
