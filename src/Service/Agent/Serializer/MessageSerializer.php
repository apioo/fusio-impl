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

use Fusio\Model\Backend\AgentContent;
use Fusio\Model\Backend\AgentContentBinary;
use Fusio\Model\Backend\AgentContentText;
use Fusio\Model\Backend\AgentContentToolCall;
use Fusio\Model\Backend\AgentContentToolCallFunction;
use PSX\Json\Parser;
use Symfony\AI\Platform\Message\Content\Collection;
use Symfony\AI\Platform\Message\Content\ContentInterface;
use Symfony\AI\Platform\Message\Content\DocumentUrl;
use Symfony\AI\Platform\Message\Content\File;
use Symfony\AI\Platform\Message\Content\ImageUrl;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\ToolCallMessage;
use Symfony\AI\Platform\Message\UserMessage;

/**
 * MessageSerializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class MessageSerializer
{
    /**
     * @return array<AgentContent>
     */
    public function serialize(MessageInterface $message): array
    {
        if ($message instanceof ToolCallMessage) {
            $toolCall = $message->getToolCall();

            $function = new AgentContentToolCallFunction();
            $function->setName($toolCall->getName());
            $function->setArguments(Parser::encode($toolCall->getArguments()));
            $function->setId($toolCall->getId());

            $result = new AgentContentToolCall();
            $result->setType('tool_call');
            $result->setFunctions([$function]);

            return [$result];
        } elseif ($message instanceof UserMessage) {
            $result = [];
            foreach ($message->getContent() as $content) {
                foreach ($this->serializeContent($content) as $entry) {
                    $result[] = $entry;
                }
            }

            return $result;
        } else {
            $result = new AgentContentText();
            $result->setType('text');
            $result->setContent($message->getContent());

            return [$result];
        }
    }

    /**
     * @return array<AgentContent>
     */
    private function serializeContent(ContentInterface $content): array
    {
        if ($content instanceof File) {
            $result = new AgentContentBinary();
            $result->setType('binary');
            $result->setMime($content->getFormat());
            $result->setData($content->asBase64());

            return [$result];
        } elseif ($content instanceof Text) {
            $result = new AgentContentText();
            $result->setType('text');
            $result->setContent($content->getText());

            return [$result];
        } elseif ($content instanceof ImageUrl || $content instanceof DocumentUrl) {
            $result = new AgentContentText();
            $result->setType('text');
            $result->setContent($content->getUrl());

            return [$result];
        } elseif ($content instanceof Collection) {
            $result = [];
            foreach ($content->getContent() as $childContent) {
                foreach ($this->serializeContent($childContent) as $message) {
                    $result[] = $message;
                }
            }

            return $result;
        }

        return [];
    }
}
