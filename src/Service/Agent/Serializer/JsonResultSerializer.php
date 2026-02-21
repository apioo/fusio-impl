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
use Fusio\Model\Backend\AgentContentObject;
use stdClass;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;

/**
 * ResultSerializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class JsonResultSerializer extends ResultSerializer
{
    public function serialize(ResultInterface $result): AgentContent
    {
        if ($result instanceof TextResult) {
            $content = $result->getContent();

            // in case we get a text response we need to try to parse the text as JSON since LLMs can produce all kind
            // of strange output, we try to remove all noice to get to the JSON payload
            $content = trim($content);
            $content = $this->removeMarkdownSyntax($content);

            $payload = json_decode($content);
            if ($payload instanceof stdClass) {
                $object = new AgentContentObject();
                $object->setType('object');
                $object->setPayload($payload);
                return $object;
            }
        }

        return parent::serialize($result);
    }

    private function removeMarkdownSyntax(string $content): string
    {
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        } elseif (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }

        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }

        return $content;
    }
}
