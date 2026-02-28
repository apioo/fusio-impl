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

namespace Fusio\Impl\Backend\Action\Connection\Agent;

use Fusio\Engine\Connector;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Agent\Serializer\ResultSerializer;
use Fusio\Impl\Service\Agent\Unserializer\MessageUnserializer;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Model\Backend\AgentInput;
use Fusio\Model\Backend\AgentOutput;
use PSX\Http\Environment\HttpResponse;

/**
 * Send
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Send extends AgentAbstract
{
    public function __construct(private MessageUnserializer $messageUnserializer, private ResultSerializer $resultSerializer, Connector $connector, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($connector, $frameworkConfig);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $this->assertConnectionEnabled();

        $agent = $this->getConnection($request);
        $payload = $request->getPayload();

        assert($payload instanceof AgentInput);

        $messages = $this->messageUnserializer->unserialize($payload->getInput());

        $options = [
            'temperature' => 0.4
        ];

        $result = $agent->call($messages, $options);

        $output = new AgentOutput();
        $output->setOutput($this->resultSerializer->serialize($result));

        return new HttpResponse(200, [], $output);
    }
}
