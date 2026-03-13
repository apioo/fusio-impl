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

namespace Fusio\Impl\Tests\Service;

use Fusio\Impl\Service\Agent\Serializer\JsonResultSerializer;
use Fusio\Model\Backend\AgentContentObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PSX\Json\Parser;
use Symfony\AI\Platform\Result\TextResult;

/**
 * JsonResultSerializerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonResultSerializerTest extends TestCase
{
    #[DataProvider('testProvider')]
    public function testSerializeText(string $content, string $expect): void
    {
        $serializer = new JsonResultSerializer();

        $result = $serializer->serialize(new TextResult($content));

        $this->assertInstanceOf(AgentContentObject::class, $result);
        $this->assertJsonStringEqualsJsonString($expect, Parser::encode($result->getPayload()));
    }

    public static function testProvider(): array
    {
        return [
            ['{}', '{}'],
            [Parser::encode(['foo' => 'bar']), Parser::encode(['foo' => 'bar'])],
            ['```json' . "\n" . Parser::encode(['foo' => 'bar']) . "\n" . '```', Parser::encode(['foo' => 'bar'])],
            ['<think>...</think>' . "\n" . Parser::encode(['foo' => 'bar']), Parser::encode(['foo' => 'bar'])],
            [Parser::encode(['foo' => 'bar']) . '## Summary', Parser::encode(['foo' => 'bar'])],
        ];
    }
}
