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

namespace Fusio\Impl\Tests\System;

use Fusio\Impl\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * TypeAPITest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TypeAPITest extends DbTestCase
{
    #[DataProvider('providerFilter')]
    public function testGenerate(string $category)
    {
        $response = $this->sendRequest('/system/generator/typeapi?filter=' . $category, 'GET', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();
        $expect = __DIR__ . '/resources/typeapi_' . $category . '.json';
        $actual = __DIR__ . '/resources/typeapi_' . $category . '_actual.json';

        file_put_contents($actual, $body);

        $this->assertJsonFileEqualsJsonFile($expect, $actual);
    }

    public static function providerFilter(): array
    {
        return [
            ['app'],
            ['fusio'],
        ];
    }
}
