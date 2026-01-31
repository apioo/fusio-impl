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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Impl\Service\Agent\Intent\ActionIntent;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Prompt;
use Mcp\Schema\PromptArgument;

/**
 * PromptLoader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class PromptLoader
{
    public function __construct(private ActionIntent $actionIntent)
    {
    }

    public function load(RegistryInterface $registry): void
    {
        $arguments = [];
        $arguments[] = new PromptArgument('logic', 'The business logic of the action');

        $prompt = new Prompt('action-development', 'Prompt which helps to create a new action', $arguments);

        $registry->registerPrompt($prompt, function (string $logic): array {
            return [
                ['role' => 'system', 'content' => $this->actionIntent->getMessage()],
                ['role' => 'user', 'content' => $logic],
            ];
        });
    }
}
