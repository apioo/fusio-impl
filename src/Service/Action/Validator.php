<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Action name must not be empty');
        }

        $class = $action->getClass();
        if ($class !== null) {
            $this->assertClass($class);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Action class must not be empty');
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
