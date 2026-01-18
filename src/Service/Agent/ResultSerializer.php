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

use PSX\Http\Exception\InternalServerErrorException;
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
    public function serialize(ResultInterface $result): array
    {
        $body = [];
        if ($result instanceof BinaryResult) {
            $body['type'] = 'binary';
            $body['mime'] = $result->getMimeType();
            $body['data'] = $result->toBase64();
        } elseif ($result instanceof ChoiceResult) {
            $items = [];
            foreach ($result->getContent() as $item) {
                $items[] = $this->serialize($item);
            }

            $body['type'] = 'choice';
            $body['items'] = $items;
        } elseif ($result instanceof ObjectResult) {
            $body['type'] = 'object';
            $body['payload'] = $result->getContent();
        } elseif ($result instanceof StreamResult) {
            $events = [];
            foreach ($result->getContent() as $event) {
                $events[] = $event;
            }

            $body['type'] = 'stream';
            $body['events'] = $events;
        } elseif ($result instanceof TextResult) {
            $body['type'] = 'text';
            $body['content'] = $result->getContent();
        } elseif ($result instanceof ToolCallResult) {
            $body['type'] = 'tool_call';
            $body['calls'] = $result->getContent();
        } else {
            throw new InternalServerErrorException('Provided an unsupported result type: ' . $result::class);
        }

        $body['metadata'] = $result->getMetadata();

        return $body;
    }
}
