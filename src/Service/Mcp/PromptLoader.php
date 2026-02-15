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
use Fusio\Impl\Service\Agent\Intent\ArchitectIntent;
use Fusio\Impl\Service\Agent\Intent\SchemaIntent;
use Mcp\Capability\RegistryInterface;
use Mcp\Schema\Prompt;
use Mcp\Schema\PromptArgument;
use RuntimeException;

/**
 * PromptLoader
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class PromptLoader
{
    public function __construct(
        private ActionIntent $actionIntent,
        private SchemaIntent $schemaIntent,
        private ArchitectIntent $architectIntent,
    ) {
    }

    public function load(RegistryInterface $registry): void
    {
        $this->loadAction($registry);
        $this->loadSchema($registry);
        $this->loadArchitect($registry);
    }

    private function loadAction(RegistryInterface $registry): void
    {
        $arguments = [];
        $arguments[] = new PromptArgument('logic', 'Describe the business logic of a new action', true);

        $prompt = new Prompt('new-action', 'Prompt which helps to create a new action', $arguments);

        $registry->registerPrompt($prompt, function (array $arguments): array {
            $logic = $arguments['logic'] ?? null;
            if (!is_string($logic) || $logic === '') {
                throw new RuntimeException('Could not extract logic from arguments, got: ' . var_export($arguments, true));
            }

            return [
                ['role' => 'assistant', 'content' => $this->actionIntent->getMessage()],
                ['role' => 'user', 'content' => $logic],
            ];
        });
    }

    private function loadSchema(RegistryInterface $registry): void
    {
        $arguments = [];
        $arguments[] = new PromptArgument('structure', 'Describe the structure of a new schema', true);

        $prompt = new Prompt('new-schema', 'Prompt which helps to create a new schema', $arguments);

        $registry->registerPrompt($prompt, function (array $arguments): array {
            $structure = $arguments['structure'] ?? null;
            if (!is_string($structure) || $structure === '') {
                throw new RuntimeException('Could not extract structure from arguments, got: ' . var_export($arguments, true));
            }

            return [
                ['role' => 'assistant', 'content' => $this->schemaIntent->getMessage()],
                ['role' => 'user', 'content' => $structure],
            ];
        });
    }

    private function loadArchitect(RegistryInterface $registry): void
    {
        $arguments = [];
        $arguments[] = new PromptArgument('description', 'Describe what you want to build from a high level view', true);

        $prompt = new Prompt('architect', 'Prompt which helps to create complete systems from a high level view', $arguments);

        $registry->registerPrompt($prompt, function (array $arguments): array {
            $description = $arguments['description'] ?? null;
            if (!is_string($description) || $description === '') {
                throw new RuntimeException('Could not extract description from arguments, got: ' . var_export($arguments, true));
            }

            return [
                ['role' => 'assistant', 'content' => $this->architectIntent->getMessage()],
                ['role' => 'user', 'content' => $description],
            ];
        });
    }
}
