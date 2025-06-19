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

namespace Fusio\Impl\Tests\Backend\Api\Generator;

use Fusio\Impl\Tests\Service\Generator\TestProvider;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ProviderTest extends ProviderTestCase
{
    protected function getProviderClass(): string
    {
        return TestProvider::class;
    }

    protected function getProviderConfig(): array
    {
        return [
            'table' => 'foobar'
        ];
    }

    protected function getExpectChangelog(): string
    {
        return file_get_contents(__DIR__ . '/resource/changelog_test.json');
    }

    protected function getExpectForm(): string
    {
        return <<<'JSON'
{
    "element": [
        {
            "element": "input",
            "name": "table",
            "title": "Table",
            "help": null,
            "type": "text"
        }
    ]
}
JSON;
    }
}
