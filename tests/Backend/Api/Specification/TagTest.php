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

namespace Fusio\Impl\Tests\Backend\Specification;

use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;

/**
 * TagTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TagTest extends DbTestCase
{
    public function testPost(): void
    {
        $response = $this->sendRequest('/backend/specification/tag', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Specification tag successfully created"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $result = $this->connection->fetchAllAssociative('SELECT commit_id, user_id, version FROM fusio_action_tag');
        foreach ($result as $row) {
            $this->assertGreaterThanOrEqual(1, $row['commit_id']);
            $this->assertGreaterThanOrEqual(1, $row['user_id']);
            $this->assertSame('0.1.1', $row['version']);
        }

        $result = $this->connection->fetchAllAssociative('SELECT commit_id, user_id, version FROM fusio_schema_tag');
        foreach ($result as $row) {
            $this->assertGreaterThanOrEqual(1, $row['commit_id']);
            $this->assertGreaterThanOrEqual(1, $row['user_id']);
            $this->assertSame('0.1.1', $row['version']);
        }
    }
}
