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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Connector;
use Fusio\Impl\Service\Mcp\Prompts;
use Fusio\Model\Backend\Action;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionPrompt;
use Mcp\Types\GetPromptRequestParams;
use PSX\Http\Exception\InternalServerErrorException;
use SdkFabric\Openai\Client;
use SdkFabric\Openai\CompletionRequest;

/**
 * Prompt
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Prompt
{
    public function __construct(private Connector $connector, private Prompts $prompts)
    {
    }

    public function prompt(ActionPrompt $prompt): Action
    {
        $client = $this->getOpenAIClient();

        $result = $this->prompts->get(new GetPromptRequestParams('backend-action-create'));

        $request = new CompletionRequest();
        $response = $client->completions()->create($request);

        $code = null;

        $name = '';
        $config = new ActionConfig();
        $config->put('code', $code);

        $action = new Action();
        $action->setClass($prompt->getClass());
        $action->setName($name);
        $action->setConfig($config);

        return $action;
    }

    private function getOpenAIClient(): Client
    {
        $client = $this->connector->getConnection('');
        if (!$client instanceof Client) {
            throw new InternalServerErrorException('Could not find OpenAI connection');
        }

        return $client;
    }
}
