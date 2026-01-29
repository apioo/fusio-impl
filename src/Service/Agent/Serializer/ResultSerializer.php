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

use Fusio\Model\Backend\AgentMessage;
use Fusio\Model\Backend\AgentMessageBinary;
use Fusio\Model\Backend\AgentMessageChoice;
use Fusio\Model\Backend\AgentMessageObject;
use Fusio\Model\Backend\AgentMessageStream;
use Fusio\Model\Backend\AgentMessageText;
use Fusio\Model\Backend\AgentMessageToolCall;
use Fusio\Model\Backend\AgentMessageToolCallFunction;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Json\Parser;
use Symfony\AI\Platform\Result\BinaryResult;
use Symfony\AI\Platform\Result\ChoiceResult;
use Symfony\AI\Platform\Result\ObjectResult;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\StreamResult;
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
    public function serialize(ResultInterface $result): AgentMessage
    {
        if ($result instanceof BinaryResult) {
            $message = new AgentMessageBinary();
            $message->setType('binary');
            $message->setMime($result->getMimeType());
            $message->setData($result->toBase64());
        } elseif ($result instanceof ChoiceResult) {
            $items = [];
            foreach ($result->getContent() as $item) {
                $items[] = $this->serialize($item);
            }

            $message = new AgentMessageChoice();
            $message->setType('choice');
            $message->setItems($items);
        } elseif ($result instanceof ObjectResult) {
            $message = new AgentMessageObject();
            $message->setType('object');
            $message->setPayload($result->getContent());
        } elseif ($result instanceof StreamResult) {
            $events = [];
            foreach ($result->getContent() as $event) {
                $events[] = $event;
            }

            $message = new AgentMessageStream();
            $message->setType('stream');
            $message->setEvents($events);
        } elseif ($result instanceof TextResult) {
            $message = new AgentMessageText();
            $message->setType('text');
            $message->setContent($result->getContent());
        } elseif ($result instanceof ToolCallResult) {
            $functions = [];
            foreach ($result->getContent() as $toolCall) {
                $function = new AgentMessageToolCallFunction();
                $function->setName($toolCall->getName());
                $function->setArguments(Parser::encode($toolCall->getArguments()));
                $function->setId($toolCall->getId());
                $functions[] = $function;
            }

            $message = new AgentMessageToolCall();
            $message->setType('tool_call');
            $message->setFunctions($functions);
        } else {
            throw new InternalServerErrorException('Provided an unsupported result type: ' . $result::class);
        }

        return $message;
    }
}
