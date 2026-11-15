<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
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

namespace Fusio\Impl\Tests\Backend\Api\Agent;

use Fusio\Impl\Messenger\ExecuteAgentTask;
use Fusio\Impl\Service\Agent\Task\Executor;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Authorization;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\Environment;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * TaskTest
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TaskTest extends DbTestCase
{
    public function testCreateAndExecute(): void
    {
        $agentId = Fixture::getReference('fusio_agent', 'Fusio-Schema')->resolve($this->connection);

        $taskId = $this->createTask($agentId, [
            'type' => 'object',
            'payload' => [
                'name' => 'Agent-Task-Item',
                'types' => [[
                    'name' => 'AgentTaskItem',
                    'type' => 'object',
                    'description' => 'A test item',
                    'parent' => null,
                    'reference' => null,
                    'properties' => [
                        ['name' => 'id', 'type' => 'integer', 'reference' => null, 'description' => 'The id', 'nullable' => false, 'deprecated' => false],
                        ['name' => 'tags', 'type' => 'array', 'reference' => 'string', 'description' => 'The tags', 'nullable' => false, 'deprecated' => false],
                    ],
                ]],
                'root' => 0,
            ],
        ]);

        $this->assertSame(Table\Agent\Task::STATUS_PENDING, $this->getStatus($taskId));

        $this->consumeMessages();

        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task/' . $taskId, 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertSame(200, $response->getStatusCode(), $body);
        $this->assertSame('completed', $data->status, $body);
        $this->assertSame('Schema successfully created', $data->message, $body);
        $this->assertSame('Schema create: Agent-Task-Item', $data->steps[0]->name ?? null, $body);
        $this->assertTrue($data->steps[0]->success ?? false, $body);

        $source = $this->connection->fetchOne('SELECT source FROM fusio_schema WHERE name = :name', ['name' => 'Agent-Task-Item']);
        $this->assertIsString($source);

        $schema = json_decode($source, true);
        $this->assertSame('AgentTaskItem', $schema['root'] ?? null);
        $this->assertSame(['type' => 'array', 'schema' => ['type' => 'string']], array_intersect_key($schema['definitions']['AgentTaskItem']['properties']['tags'] ?? [], ['type' => 0, 'schema' => 0]));
    }

    public function testCollection(): void
    {
        $agentId = Fixture::getReference('fusio_agent', 'Fusio-Schema')->resolve($this->connection);

        $this->createTask($agentId, ['type' => 'object', 'payload' => ['name' => 'Foo', 'types' => [], 'root' => 0]]);

        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertSame(200, $response->getStatusCode(), $body);
        $this->assertSame(1, $data->totalResults, $body);
        $this->assertSame('pending', $data->entry[0]->status, $body);
    }

    public function testCancel(): void
    {
        $agentId = Fixture::getReference('fusio_agent', 'Fusio-Schema')->resolve($this->connection);

        $taskId = $this->createTask($agentId, ['type' => 'object', 'payload' => ['name' => 'Foo', 'types' => [], 'root' => 0]]);

        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task/' . $taskId, 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ]);

        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode(), $body);
        $this->assertSame(Table\Agent\Task::STATUS_CANCELED, $this->getStatus($taskId));

        // a canceled task must not be executed by the worker
        $this->consumeMessages();

        $this->assertSame(Table\Agent\Task::STATUS_CANCELED, $this->getStatus($taskId));

        // a finished task can not be canceled again
        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task/' . $taskId, 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ]);

        $this->assertSame(400, $response->getStatusCode(), (string) $response->getBody());
    }

    public function testCreateWithoutAction(): void
    {
        $agentId = Fixture::getReference('fusio_agent', 'agent-test')->resolve($this->connection);

        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ], json_encode([
            'item' => ['type' => 'text', 'content' => 'foo'],
        ]));

        $body = (string) $response->getBody();

        $this->assertSame(400, $response->getStatusCode(), $body);
        $this->assertStringContainsString('The agent has no action configured', $body);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function createTask(int $agentId, array $item): int
    {
        $response = $this->sendRequest('/backend/agent/' . $agentId . '/task', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ], json_encode([
            'item' => $item,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertSame(202, $response->getStatusCode(), $body);
        $this->assertTrue($data->success, $body);

        return (int) $data->id;
    }

    private function getStatus(int $taskId): int
    {
        return (int) $this->connection->fetchOne('SELECT status FROM fusio_agent_task WHERE id = :id', ['id' => $taskId]);
    }

    /**
     * Simulates the messenger worker, we read all messages from the transport and execute the agent tasks
     */
    private function consumeMessages(): void
    {
        $transport = Environment::getService(TransportInterface::class);
        $executor = Environment::getService(Executor::class);

        foreach ($transport->get() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof ExecuteAgentTask) {
                $executor->execute($message);
            }

            $transport->ack($envelope);
        }
    }
}
