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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Messenger\ExecuteAgentTask;
use Fusio\Impl\Service\Agent\Task\Executor;
use Fusio\Impl\Tests\Authorization;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * A2ATest
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class A2ATest extends DbTestCase
{
    /**
     * The test agent connection only responds to this prompt
     */
    private const string PROMPT = 'What is the meaning of life?';

    protected function setUp(): void
    {
        parent::setUp();

        // we only expose the architect agent, so we configure it like the test agent which is connected to the test
        // connection. The architect has an action by default, we remove it so that the agent responds with a message
        $source = $this->connection->fetchAssociative('SELECT connection_id, introduction, introduction_action, tools FROM fusio_agent WHERE name = :name', ['name' => 'agent-test']);
        $this->connection->update('fusio_agent', $source + ['action' => null], ['name' => 'Fusio-Architect']);
    }

    public function testMissingAuthorization(): void
    {
        $response = $this->sendRequest('/a2a/v1', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'A2A-Version' => '1.0',
        ], $this->rpc('ListTasks', new \stdClass()));

        $this->assertSame(401, $response->getStatusCode(), (string) $response->getBody());
        $this->assertStringContainsString('Bearer', $response->getHeader('WWW-Authenticate'));
    }

    public function testInvalidScope(): void
    {
        $response = $this->sendRequest('/a2a/v1', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => Authorization::FOOAPP_CONSUMER,
            'A2A-Version' => '1.0',
        ], $this->rpc('ListTasks', new \stdClass()));

        $this->assertSame(401, $response->getStatusCode(), (string) $response->getBody());
    }

    public function testVersionNotSupported(): void
    {
        $data = $this->call('ListTasks', new \stdClass(), '0.3');

        $this->assertSame(-32009, $data->error->code);
        $this->assertSame('VERSION_NOT_SUPPORTED', $data->error->data[0]->reason);
        $this->assertSame('a2a-protocol.org', $data->error->data[0]->domain);
    }

    public function testInvalidJson(): void
    {
        $response = $this->sendRequest('/a2a/v1', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
            'A2A-Version' => '1.0',
        ], '{foo');

        $data = json_decode((string) $response->getBody());

        $this->assertSame(-32700, $data->error->code);
    }

    public function testUnsupportedMethods(): void
    {
        $this->assertSame(-32601, $this->call('Foo', new \stdClass())->error->code);
        $this->assertSame(-32004, $this->call('SendStreamingMessage', new \stdClass())->error->code);
        $this->assertSame(-32004, $this->call('SubscribeToTask', (object) ['id' => '1'])->error->code);
        $this->assertSame(-32004, $this->call('GetExtendedAgentCard', new \stdClass())->error->code);
        $this->assertSame(-32003, $this->call('CreateTaskPushNotificationConfig', new \stdClass())->error->code);
    }

    public function testSendMessageReturnsMessage(): void
    {
        $data = $this->call('SendMessage', [
            'message' => $this->newMessage(self::PROMPT),
        ]);

        $this->assertObjectNotHasProperty('error', $data, json_encode($data));

        $message = $data->result->message;
        $this->assertSame('ROLE_AGENT', $message->role);
        $this->assertSame('The answer ist: 42', $message->parts[0]->text);
        $this->assertNotEmpty($message->contextId);
        $this->assertNotEmpty($message->messageId);
        $this->assertObjectNotHasProperty('task', $data->result);

        // a follow-up message in the same context
        $data = $this->call('SendMessage', [
            'message' => $this->newMessage(self::PROMPT, $message->contextId),
        ]);

        $this->assertSame($message->contextId, $data->result->message->contextId, json_encode($data));

        // a context of a different agent can not be continued
        $contextId = $this->connection->fetchOne('SELECT chat_id FROM fusio_agent_message WHERE agent_id = (SELECT id FROM fusio_agent WHERE name = :name) LIMIT 1', ['name' => 'agent-test']);
        $this->assertNotEmpty($contextId);

        $data = $this->call('SendMessage', [
            'message' => $this->newMessage(self::PROMPT, $contextId),
        ]);

        $this->assertSame(-32602, $data->error->code, json_encode($data));
    }

    public function testSendMessageInvalidParams(): void
    {
        $this->assertSame(-32602, $this->call('SendMessage', new \stdClass())->error->code);
        $this->assertSame(-32602, $this->call('SendMessage', ['message' => ['messageId' => 'foo', 'role' => 'ROLE_AGENT', 'parts' => [['text' => 'foo']]]])->error->code);
        $this->assertSame(-32005, $this->call('SendMessage', ['message' => $this->newMessage('foo'), 'configuration' => ['acceptedOutputModes' => ['image/png']]])->error->code);
        $this->assertSame(-32001, $this->call('SendMessage', ['message' => $this->newMessage('foo', null, '9999')])->error->code);
    }

    public function testTaskLifecycle(): void
    {
        // the action agent executor fails on the test response, so we can test the complete lifecycle of a failed task
        $this->connection->update('fusio_agent', ['action' => 'php+class://Fusio.Impl.Backend.Action.Agent.Executor.Action'], ['name' => 'Fusio-Architect']);

        $data = $this->call('SendMessage', [
            'message' => $this->newMessage(self::PROMPT),
            'configuration' => ['returnImmediately' => true],
        ]);

        $this->assertObjectNotHasProperty('error', $data, json_encode($data));

        $task = $data->result->task;
        $this->assertSame('TASK_STATE_SUBMITTED', $task->status->state);
        $this->assertNotEmpty($task->contextId);
        $this->assertSame('The answer ist: 42', $task->artifacts[0]->parts[0]->text);
        $this->assertSame('ROLE_USER', $task->history[0]->role);
        $this->assertSame('ROLE_AGENT', $task->history[1]->role);

        $this->consumeMessages();

        $data = $this->call('GetTask', ['id' => $task->id, 'historyLength' => 0]);

        $this->assertSame('TASK_STATE_FAILED', $data->result->status->state, json_encode($data));
        $this->assertSame('The action agent returned no action name', $data->result->status->message->parts[0]->text);
        $this->assertObjectNotHasProperty('history', $data->result);

        // a message to a terminal task is not supported
        $data = $this->call('SendMessage', ['message' => $this->newMessage('foo', $task->contextId, $task->id)]);
        $this->assertSame(-32004, $data->error->code);

        // a terminal task can not be canceled
        $data = $this->call('CancelTask', ['id' => $task->id]);
        $this->assertSame(-32002, $data->error->code);

        $data = $this->call('ListTasks', ['contextId' => $task->contextId]);

        $this->assertSame(1, $data->result->totalSize, json_encode($data));
        $this->assertSame('', $data->result->nextPageToken);
        $this->assertSame(50, $data->result->pageSize);
        $this->assertSame($task->id, $data->result->tasks[0]->id);
        $this->assertObjectNotHasProperty('artifacts', $data->result->tasks[0]);
    }

    public function testCancelTask(): void
    {
        $this->connection->update('fusio_agent', ['action' => 'php+class://Fusio.Impl.Backend.Action.Agent.Executor.Action'], ['name' => 'Fusio-Architect']);

        $data = $this->call('SendMessage', [
            'message' => $this->newMessage(self::PROMPT),
            'configuration' => ['returnImmediately' => true],
        ]);

        $taskId = $data->result->task->id;

        $data = $this->call('CancelTask', ['id' => $taskId]);

        $this->assertSame('TASK_STATE_CANCELED', $data->result->status->state, json_encode($data));

        $data = $this->call('ListTasks', ['status' => 'TASK_STATE_CANCELED', 'pageSize' => 1, 'includeArtifacts' => true]);

        $this->assertSame(1, $data->result->totalSize, json_encode($data));
        $this->assertSame($taskId, $data->result->tasks[0]->id);
        $this->assertNotEmpty($data->result->tasks[0]->artifacts);

        $this->assertSame(-32001, $this->call('GetTask', ['id' => '9999'])->error->code);
        $this->assertSame(-32602, $this->call('ListTasks', ['pageSize' => 500])->error->code);
    }

    /**
     * @return array<string, mixed>
     */
    private function newMessage(string $text, ?string $contextId = null, ?string $taskId = null): array
    {
        return array_filter([
            'messageId' => uniqid('msg-'),
            'contextId' => $contextId,
            'taskId' => $taskId,
            'role' => 'ROLE_USER',
            'parts' => [['text' => $text]],
        ], fn($value) => $value !== null);
    }

    private function call(string $method, mixed $params, string $version = '1.0'): object
    {
        $response = $this->sendRequest('/a2a/v1', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
            'A2A-Version' => $version,
        ], $this->rpc($method, $params));

        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode(), $body);

        return json_decode($body);
    }

    private function rpc(string $method, mixed $params): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ]);
    }

    private function consumeMessages(): void
    {
        $transport = Environment::getService(TransportInterface::class);
        $executor = Environment::getService(Executor::class);

        foreach ($transport->get() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof ExecuteAgentTask) {
                $executor->execute($message->getTaskId(), $message->getContext());
            }

            $transport->ack($envelope);
        }
    }
}
