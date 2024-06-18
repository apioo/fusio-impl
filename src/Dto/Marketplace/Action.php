<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Dto\Marketplace;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Action extends ObjectAbstract
{
    private string $class;
    private \stdClass $config;

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getConfig(): \stdClass
    {
        return $this->config;
    }

    public function setConfig(\stdClass $config): void
    {
        $this->config = $config;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'class' => $this->class,
            'config' => $this->config,
        ]);
    }

    public static function fromObject(\stdClass $data): static
    {
        $action = parent::fromObject($data);

        if (isset($data->class) && is_string($data->class)) {
            $action->setClass($data->class);
        }

        if (isset($data->config) && $data->config instanceof \stdClass) {
            $action->setConfig($data->config);
        }

        return $action;
    }
}
