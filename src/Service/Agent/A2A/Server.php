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

namespace Fusio\Impl\Service\Agent\A2A;

use DateTimeImmutable;
use DateTimeZone;
use Fusio\Engine\Context as EngineContext;
use Fusio\Engine\ContextInterface;
use Fusio\Impl\Exception\A2AException;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Agent\Sender;
use Fusio\Impl\Service\Agent\Task as TaskService;
use Fusio\Impl\Service\Rate\Limiter;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model\Agent\Input;
use Fusio\Model\Agent\ItemText;
use Fusio\Model\Backend\AgentTaskCreate;
use InvalidArgumentException;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Json\Rpc\Context as RpcContext;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Exception\ServerErrorException;
use PSX\Record\Record;
use PSX\Sql\Condition;
use stdClass;
use Throwable;

/**
 * Implements the JSON-RPC binding of the A2A protocol. We only expose the architect agent of the instance, since it
 * is the most useful agent to build complete API operations, all other agents are not available. If the agent has an action the
 * output of the agent is processed by an agent task in the background, which is exposed as A2A task, otherwise the
 * agent directly responds with a message. The A2A context id is the chat id of the agent conversation
 *
 * @see https://a2a-protocol.org/latest/specification/
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Server
{
    public const PROTOCOL_VERSION = '1.0';

    /**
     * The operation which is used to authenticate every A2A request
     */
    public const OPERATION_AUTHENTICATION = 'backend.agent.message.submit';

    /**
     * Max time in seconds a blocking SendMessage call waits for the background task
     */
    private const BLOCKING_TIMEOUT = 60;

    public function __construct(
        private ContextFactory $contextFactory,
        private TokenValidator $tokenValidator,
        private Limiter $limiter,
        private Table\Operation $operationTable,
        private Table\Agent $agentTable,
        private Table\Agent\Task $taskTable,
        private Table\Agent\Message $messageTable,
        private Sender $sender,
        private TaskService $taskService,
        private Converter $converter,
        private FrameworkConfig $frameworkConfig,
    ) {
    }

    /**
     * Authenticates the current request, this is called by the controller before the JSON-RPC request is handled so
     * that a missing or invalid token results in an HTTP 401 response including an authentication challenge
     *
     * @throws StatusCode\UnauthorizedException
     */
    public function authenticate(): void
    {
        $context = $this->contextFactory->getActive();
        $context->setOperation($this->getOperation(self::OPERATION_AUTHENTICATION));

        $this->tokenValidator->assertAuthorization($context->getAuthorization(), $context);

        if ($context->getUser()->isAnonymous()) {
            throw new StatusCode\UnauthorizedException('Could not authorize request', 'Bearer');
        }
    }

    /**
     * @param array<string, mixed>|stdClass|null $params
     */
    public function __invoke(string $method, array|stdClass|null $params, RpcContext $rpcContext): mixed
    {
        if (is_array($params)) {
            throw new InvalidRequestException('Params as array (by-position) are not supported, please use params as object (by-name)');
        }

        $version = $rpcContext->get('version');
        if (is_string($version) && $version !== '' && $version !== self::PROTOCOL_VERSION) {
            throw A2AException::versionNotSupported($version);
        }

        $arguments = $params instanceof stdClass ? Record::fromObject($params) : new Record();

        try {
            return match ($method) {
                'SendMessage' => $this->sendMessage($arguments),
                'GetTask' => $this->getTask($arguments),
                'ListTasks' => $this->listTasks($arguments),
                'CancelTask' => $this->cancelTask($arguments),
                'SendStreamingMessage', 'SubscribeToTask' => throw A2AException::unsupportedOperation('Streaming is not supported by this agent'),
                'CreateTaskPushNotificationConfig', 'GetTaskPushNotificationConfig', 'ListTaskPushNotificationConfigs', 'DeleteTaskPushNotificationConfig' => throw A2AException::pushNotificationNotSupported(),
                'GetExtendedAgentCard' => throw A2AException::unsupportedOperation('The extended agent card is not supported by this agent'),
                default => throw new MethodNotFoundException('Method not found'),
            };
        } catch (ServerErrorException $e) {
            throw $e;
        } catch (StatusCode\BadRequestException $e) {
            throw A2AException::invalidParams($e->getMessage());
        } catch (StatusCode\UnauthorizedException|StatusCode\ForbiddenException|StatusCode\PaymentRequiredException $e) {
            throw new A2AException(A2AException::UNAUTHORIZED, $e->getMessage(), $e);
        } catch (StatusCode\StatusCodeException $e) {
            throw new A2AException(A2AException::INTERNAL_ERROR, $e->getMessage(), $e);
        } catch (Throwable $e) {
            throw new A2AException(A2AException::INTERNAL_ERROR, 'Internal error', $e);
        }
    }

    /**
     * @param Record<mixed> $arguments
     * @return array<string, mixed>
     */
    private function sendMessage(Record $arguments): array
    {
        $message = $arguments->get('message');
        if (!$message instanceof stdClass) {
            throw A2AException::invalidParams('Provided no message');
        }

        if (empty($message->messageId) || !is_string($message->messageId)) {
            throw A2AException::invalidParams('Provided message must contain a messageId');
        }

        if (($message->role ?? null) !== Converter::ROLE_USER) {
            throw A2AException::invalidParams('Provided message must have the role ' . Converter::ROLE_USER);
        }

        $parts = $message->parts ?? null;
        if (!is_array($parts) || count($parts) === 0) {
            throw A2AException::invalidParams('Provided message must contain at least one part');
        }

        $configuration = $arguments->get('configuration');
        $configuration = $configuration instanceof stdClass ? $configuration : new stdClass();

        if (!empty($configuration->taskPushNotificationConfig)) {
            throw A2AException::pushNotificationNotSupported();
        }

        $historyLength = $this->getHistoryLength($configuration->historyLength ?? null);
        $returnImmediately = ($configuration->returnImmediately ?? false) === true;

        $context = $this->authorize('backend.agent.message.submit');

        // Fusio tasks do not support interrupted states (input-required), so a task can not receive further messages
        $taskId = $message->taskId ?? null;
        if (!empty($taskId)) {
            $task = $this->findTask($taskId, $context);
            if (Table\Agent\Task::isFinished($task->getStatus())) {
                throw A2AException::unsupportedOperation('The task is in a terminal state and can not accept further messages, please send a new message without task id to start a new task in the same context');
            }

            throw A2AException::unsupportedOperation('The task does not accept further messages, please wait until the task is finished');
        }

        $contextId = $message->contextId ?? null;
        if ($contextId !== null && (!is_string($contextId) || !preg_match('/^[A-Za-z0-9._:-]{1,255}$/', $contextId))) {
            throw A2AException::invalidParams('Provided an invalid contextId');
        }

        $metadata = $this->mergeMetadata($arguments->get('metadata'), $message->metadata ?? null);
        $agent = $this->resolveAgent($contextId, $context);

        $this->assertOutputMode($agent, $configuration->acceptedOutputModes ?? null);

        $item = new ItemText();
        $item->setType('text');
        $item->setContent($this->partsToText($parts));

        $input = new Input();
        $input->setItem($item);
        $input->setPreviousId($contextId);

        $output = $this->sender->send($agent->getId(), $input, $context);

        $chatId = $output->getId();
        $outputItem = $output->getItem();
        if (empty($chatId) || $outputItem === null) {
            throw A2AException::invalidAgentResponse('The agent has returned no response');
        }

        if (empty($agent->getAction())) {
            return [
                'message' => $this->converter->newAgentMessage($outputItem, $chatId),
            ];
        }

        $taskContext = $this->authorize('backend.agent.task.create');

        $create = new AgentTaskCreate();
        $create->setItem($outputItem);

        $connection = $metadata['connection'] ?? null;
        $taskId = $this->taskService->create('' . $agent->getId(), $create, is_string($connection) ? $connection : null, $taskContext, $chatId);

        if (!$returnImmediately) {
            $this->waitForTask($taskId);
        }

        $task = $this->taskTable->find($taskId) ?? throw A2AException::taskNotFound();

        return [
            'task' => $this->converter->toTask($task, $historyLength, true),
        ];
    }

    /**
     * @param Record<mixed> $arguments
     * @return array<string, mixed>
     */
    private function getTask(Record $arguments): array
    {
        $historyLength = $this->getHistoryLength($arguments->get('historyLength'));

        $context = $this->authorize('backend.agent.task.get');
        $task = $this->findTask($arguments->get('id'), $context);

        return $this->converter->toTask($task, $historyLength, true);
    }

    /**
     * @param Record<mixed> $arguments
     * @return array<string, mixed>
     */
    private function listTasks(Record $arguments): array
    {
        $contextId = $arguments->get('contextId');
        if ($contextId !== null && !is_string($contextId)) {
            throw A2AException::invalidParams('Provided an invalid contextId');
        }

        $status = null;
        $state = $arguments->get('status');
        if ($state !== null) {
            if (!is_string($state) || !Converter::isValidState($state)) {
                throw A2AException::invalidParams('Provided an invalid status');
            }

            if ($state !== 'TASK_STATE_UNSPECIFIED') {
                // states which are never used by Fusio i.e. TASK_STATE_INPUT_REQUIRED never match a task
                $status = Converter::getStatus($state) ?? -1;
            }
        }

        $pageSize = $arguments->get('pageSize') ?? 50;
        if (!is_int($pageSize) || $pageSize < 1 || $pageSize > 100) {
            throw A2AException::invalidParams('Provided pageSize must be an integer between 1 and 100');
        }

        $pageToken = $arguments->get('pageToken');
        $startIndex = 0;
        if ($pageToken !== null && $pageToken !== '') {
            if (!is_string($pageToken) || !ctype_digit($pageToken)) {
                throw A2AException::invalidParams('Provided an invalid pageToken');
            }

            $startIndex = (int) $pageToken;
        }

        $statusTimestampAfter = null;
        $after = $arguments->get('statusTimestampAfter');
        if ($after !== null) {
            try {
                $date = is_string($after) ? new DateTimeImmutable($after) : throw new InvalidArgumentException();
                $statusTimestampAfter = LocalDateTime::from($date->setTimezone(new DateTimeZone('UTC')));
            } catch (\Exception) {
                throw A2AException::invalidParams('Provided statusTimestampAfter must be an ISO 8601 timestamp');
            }
        }

        $historyLength = $this->getHistoryLength($arguments->get('historyLength'));
        $includeArtifacts = $arguments->get('includeArtifacts') === true;

        $context = $this->authorize('backend.agent.task.getAll');
        $tenantId = $context->getTenantId();
        $userId = $context->getUser()->getId();

        $totalSize = $this->taskTable->countByUser($tenantId, $userId, $contextId, $status, $statusTimestampAfter);
        $rows = $this->taskTable->findByUser($tenantId, $userId, $contextId, $status, $statusTimestampAfter, $startIndex, $pageSize);

        $tasks = [];
        foreach ($rows as $row) {
            $tasks[] = $this->converter->toTask($row, $historyLength, $includeArtifacts);
        }

        $nextIndex = $startIndex + $pageSize;

        return [
            'tasks' => $tasks,
            'nextPageToken' => $nextIndex < $totalSize ? '' . $nextIndex : '',
            'pageSize' => $pageSize,
            'totalSize' => $totalSize,
        ];
    }

    /**
     * @param Record<mixed> $arguments
     * @return array<string, mixed>
     */
    private function cancelTask(Record $arguments): array
    {
        $context = $this->authorize('backend.agent.task.cancel');
        $task = $this->findTask($arguments->get('id'), $context);

        if (Table\Agent\Task::isFinished($task->getStatus()) || !$this->taskTable->markCanceled($task->getId())) {
            throw A2AException::taskNotCancelable();
        }

        $this->taskTable->addStep($task->getId(), 'Task canceled', false, 'The task was canceled through A2A, steps which are already running are completed');

        $task = $this->taskTable->find($task->getId()) ?? throw A2AException::taskNotFound();

        return $this->converter->toTask($task, 0, true);
    }

    /**
     * Checks whether the token of the current request is allowed to access the operation which provides the same
     * functionality at the backend API, so that A2A respects the same scopes, rate limits and costs
     */
    private function authorize(string $operationName): ContextInterface
    {
        $context = $this->contextFactory->getActive();
        $operation = $this->getOperation($operationName);
        $context->setOperation($operation);

        try {
            $this->tokenValidator->assertAuthorization($context->getAuthorization(), $context);
        } catch (StatusCode\UnauthorizedException $e) {
            throw new A2AException(A2AException::UNAUTHORIZED, $e->getMessage(), $e);
        }

        if ($context->getUser()->isAnonymous()) {
            throw new A2AException(A2AException::UNAUTHORIZED, 'Could not authorize request');
        }

        $this->limiter->assertLimit($context->getIp(), $operation, $context->getApp(), $context->getUser());

        return new EngineContext($operation->getId(), $this->frameworkConfig->getDispatchUrl(), $context->getApp(), $context->getUser(), $this->frameworkConfig->getTenantId());
    }

    private function getOperation(string $name): Table\Generated\OperationRow
    {
        $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, $name);
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new \RuntimeException('Operation ' . $name . ' does not exist');
        }

        return $operation;
    }

    /**
     * We only expose the architect agent, an existing context can only be continued if it was started with this agent
     */
    private function resolveAgent(?string $contextId, ContextInterface $context): Table\Generated\AgentRow
    {
        $agent = $this->agentTable->findOneByTenantAndType($context->getTenantId(), $context->getUser()->getCategoryId(), Table\Agent::TYPE_ARCHITECT);
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw A2AException::invalidParams('The architect agent is not available');
        }

        if ($contextId !== null) {
            $condition = Condition::withAnd();
            $condition->equals(Table\Generated\AgentMessageColumn::USER_ID, $context->getUser()->getId());
            $condition->equals(Table\Generated\AgentMessageColumn::CHAT_ID, $contextId);
            $existing = $this->messageTable->findOneBy($condition);

            if ($existing instanceof Table\Generated\AgentMessageRow && $existing->getAgentId() !== $agent->getId()) {
                throw A2AException::invalidParams('The provided context does not belong to this agent, please use a new context');
            }
        }

        return $agent;
    }

    private function findTask(mixed $taskId, ContextInterface $context): Table\Generated\AgentTaskRow
    {
        if (!is_string($taskId) || !ctype_digit($taskId)) {
            throw A2AException::taskNotFound();
        }

        $task = $this->taskTable->findOneByTenantAndUser($context->getTenantId(), $context->getUser()->getId(), (int) $taskId);
        if (!$task instanceof Table\Generated\AgentTaskRow) {
            throw A2AException::taskNotFound();
        }

        return $task;
    }

    /**
     * Converts the parts of an A2A message into a text prompt for the agent
     *
     * @param array<mixed> $parts
     */
    private function partsToText(array $parts): string
    {
        $result = [];
        foreach ($parts as $part) {
            if (!$part instanceof stdClass) {
                throw A2AException::invalidParams('Provided an invalid part');
            }

            if (isset($part->text) && is_string($part->text)) {
                $result[] = $part->text;
            } elseif (property_exists($part, 'data')) {
                $result[] = Parser::encode($part->data, JSON_PRETTY_PRINT);
            } elseif (isset($part->url) && is_string($part->url)) {
                $result[] = 'URL: ' . $part->url;
            } elseif (isset($part->raw) && is_string($part->raw)) {
                $mediaType = $part->mediaType ?? '';
                $content = base64_decode($part->raw, true);
                if ($content === false || !is_string($mediaType) || !(str_starts_with($mediaType, 'text/') || $mediaType === 'application/json')) {
                    throw A2AException::contentTypeNotSupported('Only text and JSON files are supported as raw part');
                }

                $result[] = $content;
            } else {
                throw A2AException::invalidParams('Provided part must contain either text, raw, url or data');
            }
        }

        return implode("\n\n", $result);
    }

    /**
     * @param mixed $acceptedOutputModes
     */
    private function assertOutputMode(Table\Generated\AgentRow $agent, mixed $acceptedOutputModes): void
    {
        if (!is_array($acceptedOutputModes) || count($acceptedOutputModes) === 0) {
            return;
        }

        $outputMode = !empty($agent->getOutgoing()) ? 'application/json' : 'text/plain';
        foreach ($acceptedOutputModes as $mode) {
            if ($mode === $outputMode || $mode === '*/*' || $mode === explode('/', $outputMode)[0] . '/*') {
                return;
            }
        }

        throw A2AException::contentTypeNotSupported('The agent "' . $agent->getName() . '" only produces ' . $outputMode);
    }

    private function getHistoryLength(mixed $historyLength): ?int
    {
        if ($historyLength === null) {
            return null;
        }

        if (!is_int($historyLength) || $historyLength < 0) {
            throw A2AException::invalidParams('Provided historyLength must be a positive integer');
        }

        return $historyLength;
    }

    /**
     * @return array<string, mixed>
     */
    private function mergeMetadata(mixed $requestMetadata, mixed $messageMetadata): array
    {
        $result = [];
        if ($messageMetadata instanceof stdClass) {
            $result = array_merge($result, (array) $messageMetadata);
        }

        if ($requestMetadata instanceof stdClass) {
            $result = array_merge($result, (array) $requestMetadata);
        }

        return $result;
    }

    /**
     * Waits until the task is finished, in case the messenger uses a sync transport the task is already finished
     */
    private function waitForTask(int $taskId): void
    {
        $start = time();
        while (time() - $start < self::BLOCKING_TIMEOUT) {
            $status = $this->taskTable->getCurrentStatus($taskId);
            if ($status === null || Table\Agent\Task::isFinished($status)) {
                return;
            }

            usleep(500000);
        }
    }
}
