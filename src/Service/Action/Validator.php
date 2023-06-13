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

namespace Fusio\Impl\Service\Action;

use Fusio\Adapter\Php\Action\PhpSandbox;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\ActionRow;
use Fusio\Model\Backend\Action;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Action $actionTable;
    private ConfigInterface $config;

    public function __construct(Table\Action $actionTable, ConfigInterface $config)
    {
        $this->actionTable = $actionTable;
        $this->config = $config;
    }

    public function assert(Action $action, ?ActionRow $existing = null): void
    {
        $this->assertSandboxAccess($action);

        $name = $action->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Action name must not be empty');
            }
        }

        $class = $action->getClass();
        if ($class !== null) {
            $this->assertClass($class);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Action class must not be empty');
            }
        }
    }

    private function assertName(string $name, ?ActionRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid action name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->actionTable->findOneByName($name)) {
            throw new StatusCode\BadRequestException('Action already exists');
        }
    }

    private function assertClass(string $class): void
    {
        if (empty($class)) {
            throw new StatusCode\BadRequestException('Action class must not be empty');
        }
    }

    private function assertSandboxAccess(Action $record): void
    {
        $class = ltrim((string) $record->getClass(), '\\');

        if (!$this->config->get('fusio_php_sandbox') && strcasecmp($class, PhpSandbox::class) == 0) {
            throw new StatusCode\BadRequestException('Usage of the PHP sandbox feature is disabled. To activate it set the key "fusio_php_sandbox" in the configuration.php file to "true"');
        }
    }
}
