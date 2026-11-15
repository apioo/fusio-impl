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

namespace Fusio\Impl\Exception;

use Fusio\Impl\Service\Agent\A2A\Server;
use PSX\Json\Rpc\Exception\ServerErrorException;
use Throwable;

/**
 * Represents an A2A error, the code is used as JSON-RPC error code
 *
 * @see https://a2a-protocol.org/latest/specification/
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class A2AException extends ServerErrorException
{
    public const int TASK_NOT_FOUND = -32001;
    public const int TASK_NOT_CANCELABLE = -32002;
    public const int PUSH_NOTIFICATION_NOT_SUPPORTED = -32003;
    public const int UNSUPPORTED_OPERATION = -32004;
    public const int CONTENT_TYPE_NOT_SUPPORTED = -32005;
    public const int INVALID_AGENT_RESPONSE = -32006;
    public const int EXTENDED_AGENT_CARD_NOT_CONFIGURED = -32007;
    public const int EXTENSION_SUPPORT_REQUIRED = -32008;
    public const int VERSION_NOT_SUPPORTED = -32009;

    /**
     * Implementation defined server error which is used if the authenticated client is not allowed to call a method,
     * the A2A specification reserves the range -32001 to -32099 so we use -32000
     */
    public const int UNAUTHORIZED = -32000;

    public const int INVALID_PARAMS = -32602;
    public const int INTERNAL_ERROR = -32603;

    private const array REASONS = [
        self::TASK_NOT_FOUND => 'TASK_NOT_FOUND',
        self::TASK_NOT_CANCELABLE => 'TASK_NOT_CANCELABLE',
        self::PUSH_NOTIFICATION_NOT_SUPPORTED => 'PUSH_NOTIFICATION_NOT_SUPPORTED',
        self::UNSUPPORTED_OPERATION => 'UNSUPPORTED_OPERATION',
        self::CONTENT_TYPE_NOT_SUPPORTED => 'CONTENT_TYPE_NOT_SUPPORTED',
        self::INVALID_AGENT_RESPONSE => 'INVALID_AGENT_RESPONSE',
        self::EXTENDED_AGENT_CARD_NOT_CONFIGURED => 'EXTENDED_AGENT_CARD_NOT_CONFIGURED',
        self::EXTENSION_SUPPORT_REQUIRED => 'EXTENSION_SUPPORT_REQUIRED',
        self::VERSION_NOT_SUPPORTED => 'VERSION_NOT_SUPPORTED',
    ];

    public function __construct(int $code, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the reason for the google.rpc.ErrorInfo error detail or null in case the code is no A2A specific code
     */
    public static function getReason(int $code): ?string
    {
        return self::REASONS[$code] ?? null;
    }

    public static function taskNotFound(): self
    {
        return new self(self::TASK_NOT_FOUND, 'Task not found');
    }

    public static function taskNotCancelable(): self
    {
        return new self(self::TASK_NOT_CANCELABLE, 'Task is already in a terminal state and can not be canceled');
    }

    public static function pushNotificationNotSupported(): self
    {
        return new self(self::PUSH_NOTIFICATION_NOT_SUPPORTED, 'Push notifications are not supported');
    }

    public static function unsupportedOperation(string $message): self
    {
        return new self(self::UNSUPPORTED_OPERATION, $message);
    }

    public static function contentTypeNotSupported(string $message): self
    {
        return new self(self::CONTENT_TYPE_NOT_SUPPORTED, $message);
    }

    public static function invalidAgentResponse(string $message): self
    {
        return new self(self::INVALID_AGENT_RESPONSE, $message);
    }

    public static function versionNotSupported(string $version): self
    {
        return new self(self::VERSION_NOT_SUPPORTED, 'Protocol version "' . $version . '" is not supported, supported is version "' . Server::PROTOCOL_VERSION . '"');
    }

    public static function invalidParams(string $message): self
    {
        return new self(self::INVALID_PARAMS, $message);
    }
}
