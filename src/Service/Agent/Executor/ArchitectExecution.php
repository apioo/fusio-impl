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

namespace Fusio\Impl\Service\Agent\Executor;

/**
 * Holds the state of a single blueprint execution i.e. the already generated schemas and all occurred errors
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
final class ArchitectExecution
{
    /**
     * @var array<string, string>
     */
    private array $schemas = [];

    /**
     * @var list<string>
     */
    private array $errors = [];

    public function __construct(private readonly ?string $connectionName = null)
    {
    }

    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }

    public function getSchema(string $name): ?string
    {
        return $this->schemas[$name] ?? null;
    }

    public function setSchema(string $name, string $uri): void
    {
        $this->schemas[$name] = $uri;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
