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

use Fusio\Adapter\Sql\Generator\SqlDatabase;

/**
 * SqlDatabaseTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class SqlDatabaseTest extends ProviderTestCase
{
    protected function getProviderClass(): string
    {
        return SqlDatabase::class;
    }

    protected function getProviderConfig(): array
    {
        return [
            'connection' => 2,
            'prefix' => 'app_',
        ];
    }

    protected function getExpectChangelog(): string
    {
        return file_get_contents(__DIR__ . '/resource/changelog_sqldatabase.json');
    }

    protected function getExpectSchema(): string
    {
        $data = $this->getExpectChangelog();
        $data = str_replace('schema:\/\/', 'schema:\/\/Provider_', $data);

        return $data;
    }

    protected function getExpectForm(): string
    {
        return <<<'JSON'
{
    "element": [
        {
            "element": "select",
            "name": "connection",
            "title": "Connection",
            "help": "The SQL connection which should be used",
            "options": [
                {
                    "key": "5",
                    "value": "LocalFilesystem"
                },
                {
                    "key": "3",
                    "value": "Paypal"
                },
                {
                    "key": "1",
                    "value": "System"
                },
                {
                    "key": "2",
                    "value": "Test"
                },
                {
                    "key": "4",
                    "value": "Worker"
                }
            ]
        },
        {
            "element": "input",
            "name": "prefix",
            "title": "Prefix",
            "help": "Includes only tables which start with this prefix",
            "type": "text"
        }
    ]
}
JSON;
    }
}
