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

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
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
readonly class Config
{
    public function __construct(
        private Table\Config $configTable,
        private FrameworkConfig $frameworkConfig,
        private EventDispatcherInterface $eventDispatcher
    ) {
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

    public function getInt(string $name): int
    {
        $value = $this->getValue($name);
        if ($value === null) {
            return 0;
        }

        return (int) $value;
    }

    public function getBool(string $name): bool
    {
        $value = $this->getValue($name);
        if ($value === null) {
            return false;
        }

        return (bool) $value;
    }

    public function getDateTime(string $name): ?DateTimeInterface
    {
        $value = $this->getString($name);
        if ($value === null) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (DateMalformedStringException) {
            return null;
        }
    }

    public function getString(string $name): ?string
    {
        $value = $this->getValue($name);

        return $value !== null ? (string) $value : null;
    }

    private function getValue(string $name): mixed
    {
        $config = $this->configTable->getValue($this->frameworkConfig->getTenantId(), $name);
        if (empty($config)) {
            return null;
        }

        $value = $config['value'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function convertValueToType(mixed $value, int $type): mixed
    {
        return match ($type) {
            Table\Config::FORM_NUMBER => 0 + $value,
            Table\Config::FORM_BOOLEAN => (bool)$value,
            Table\Config::FORM_DATETIME => new DateTimeImmutable($value),
            default => $value,
        };
    }
}
