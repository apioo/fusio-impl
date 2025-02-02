<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Config\UpdatedEvent;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ConfigUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Config
{
    private Table\Config $configTable;
    private FrameworkConfig $frameworkConfig;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Config $configTable, FrameworkConfig $frameworkConfig, EventDispatcherInterface $eventDispatcher)
    {
        $this->configTable = $configTable;
        $this->frameworkConfig = $frameworkConfig;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function update(string $configId, ConfigUpdate $config, UserContext $context): int
    {
        $existing = $this->configTable->findOneByIdentifier($context->getTenantId(), $configId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find config');
        }

        $existing->setValue($config->getValue());
        $this->configTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($config, $context));

        return $existing->getId();
    }

    public function getValue(string $name)
    {
        $config = $this->configTable->getValue($this->frameworkConfig->getTenantId(), $name);
        if (!empty($config)) {
            return self::convertValueToType($config['value'], $config['type']);
        } else {
            return null;
        }
    }

    public static function convertValueToType(mixed $value, int $type)
    {
        switch ($type) {
            case Table\Config::FORM_NUMBER:
                return 0 + $value;

            case Table\Config::FORM_BOOLEAN:
                return (bool) $value;

            case Table\Config::FORM_DATETIME:
                return new \DateTime($value);

            case Table\Config::FORM_TEXT:
            case Table\Config::FORM_EMAIL:
            case Table\Config::FORM_STRING:
            case Table\Config::FORM_SECRET:
            default:
                return $value;
        }
    }
}
