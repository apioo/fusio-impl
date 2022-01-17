<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Config_Update;
use Fusio\Impl\Event\Config\UpdatedEvent;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Config
{
    private Table\Config $configTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Config $configTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->configTable     = $configTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function update(int $configId, Config_Update $config, UserContext $context): int
    {
        $existing = $this->configTable->find($configId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find config');
        }

        $record = new Table\Generated\ConfigRow([
            'id'    => $existing['id'],
            'value' => $config->getValue(),
        ]);

        $this->configTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($config, $context));

        return $configId;
    }

    public function getValue(string $name)
    {
        $config = $this->configTable->getValue($name);
        if (!empty($config)) {
            return $this->convertValueToType($config['value'], $config['type']);
        } else {
            return null;
        }
    }

    protected function convertValueToType(mixed $value, int $type)
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
            default:
                return $value;
        }
    }
}
