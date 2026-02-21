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

namespace Fusio\Impl\Service\Agent\Unserializer;

use Fusio\Model\Backend\AgentContent;
use Fusio\Model\Backend\AgentContentBinary;
use Fusio\Model\Backend\AgentContentObject;
use Fusio\Model\Backend\AgentContentText;
use Fusio\Model\Backend\AgentContentToolCall;
use PSX\Http\Exception\BadRequestException;
use PSX\Json\Parser;
use Symfony\AI\Platform\Message\Content\File;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ToolCall;

/**
 * MessageUnserializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class MessageUnserializer
{
    public function unserialize(AgentContent $content): MessageBag
    {
        $messages = new MessageBag();
        if ($content instanceof AgentContentText) {
            $messages->add(Message::ofUser($content->getContent()));
        } elseif ($content instanceof AgentContentObject) {
            $messages->add(Message::ofUser(Parser::encode($content->getPayload())));
        } elseif ($content instanceof AgentContentBinary) {
            $messages->add(Message::ofUser(new File($content->getData(), $content->getMime())));
        } elseif ($content instanceof AgentContentToolCall) {
            $functions = $content->getFunctions();
            foreach ($functions as $function) {
                $toolCall = new ToolCall($function->getId(), $function->getName(), Parser::decode($function->getArguments()));

                $messages->add(Message::ofToolCall($toolCall, ''));
            }
        } else {
            throw new BadRequestException('Provided a not supported message content type');
        }

        return $messages;
    }
}
