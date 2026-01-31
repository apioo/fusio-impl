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

namespace Fusio\Impl\Service;

use Fusio\Impl\Base;
use Fusio\Impl\Service\Mcp\SessionStore;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Capability\Registry;
use Mcp\JsonRpc\MessageFactory;
use Mcp\Schema\Implementation;
use Mcp\Schema\ServerCapabilities;
use Mcp\Server;
use Mcp\Server\Configuration;
use Mcp\Server\Handler;
use Mcp\Server\Handler\Notification\NotificationHandlerInterface;
use Mcp\Server\Handler\Request\RequestHandlerInterface;
use Mcp\Server\Protocol;
use Mcp\Server\Session\SessionFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Mcp
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Mcp
{
    private const PAGINATION_LIMIT = 50;

    public function __construct(
        private Config $configService,
        private Mcp\ToolLoader $toolLoader,
        private Mcp\PromptLoader $promptLoader,
        private Mcp\ReferenceHandler $referenceHandler,
        private Table\McpSession $sessionTable,
        private FrameworkConfig $frameworkConfig,
        private ContainerInterface $container,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    public function build(): Server
    {
        $title = $this->configService->getValue('info_title') ?: 'Fusio';
        $description = $this->configService->getValue('info_description') ?: null;

        $registry = new Registry($this->eventDispatcher, $this->logger);

        $this->promptLoader->load($registry);
        $this->toolLoader->load($registry);

        $sessionFactory = new SessionFactory();
        $sessionStore = new SessionStore($this->sessionTable, $this->frameworkConfig);
        $messageFactory = MessageFactory::make();

        $capabilities = $this->serverCapabilities ?? new ServerCapabilities(
            tools: $registry->hasTools(),
            toolsListChanged: true,
            resources: $registry->hasResources() || $registry->hasResourceTemplates(),
            resourcesSubscribe: false,
            resourcesListChanged: true,
            prompts: $registry->hasPrompts(),
            promptsListChanged: true,
            logging: true,
            completions: true,
        );

        $serverInfo = new Implementation(trim($title), Base::getVersion(), $description);
        $configuration = new Configuration($serverInfo, $capabilities, self::PAGINATION_LIMIT);

        /**
         * @var array<int, RequestHandlerInterface<mixed>> $requestHandlers
         */
        $requestHandlers = [
            new Handler\Request\CallToolHandler($registry, $this->referenceHandler, $this->logger),
            new Handler\Request\CompletionCompleteHandler($registry, $this->container),
            new Handler\Request\GetPromptHandler($registry, $this->referenceHandler, $this->logger),
            new Handler\Request\InitializeHandler($configuration),
            new Handler\Request\ListPromptsHandler($registry, self::PAGINATION_LIMIT),
            new Handler\Request\ListResourcesHandler($registry, self::PAGINATION_LIMIT),
            new Handler\Request\ListResourceTemplatesHandler($registry, self::PAGINATION_LIMIT),
            new Handler\Request\ListToolsHandler($registry, self::PAGINATION_LIMIT),
            new Handler\Request\PingHandler(),
            new Handler\Request\ReadResourceHandler($registry, $this->referenceHandler, $this->logger),
            new Handler\Request\SetLogLevelHandler(),
        ];

        /**
         * @var array<int, NotificationHandlerInterface> $notificationHandlers
         */
        $notificationHandlers = [
            new Handler\Notification\InitializedHandler(),
        ];

        $protocol = new Protocol(
            requestHandlers: $requestHandlers,
            notificationHandlers: $notificationHandlers,
            messageFactory: $messageFactory,
            sessionFactory: $sessionFactory,
            sessionStore: $sessionStore,
            logger: $this->logger,
        );

        return new Server($protocol, $this->logger);
    }
}
