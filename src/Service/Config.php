<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Config
{
    /**
     * @var \Fusio\Impl\Table\Config
     */
    protected $configTable;

    public function __construct(Table\Config $configTable)
    {
        $this->configTable = $configTable;
    }

    public function update($configId, $value)
    {
        $config = $this->configTable->get($configId);

        if (!empty($config)) {
            $this->configTable->update(array(
                'id'    => $config->id,
                'value' => $value,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find config');
        }
    }

    public function getValue($name)
    {
        $condition = new Condition();
        $condition->like('name', $name);

        $config = $this->configTable->getOneBy($condition);

        if (!empty($config)) {
            return $this->convertValueToType($config['value'], $config['type']);
        } else {
            return null;
        }
    }

    protected function convertValueToType($value, $type)
    {
        switch ($type) {
            case Table\Config::FORM_NUMBER:
                return 0 + $value;

            case Table\Config::FORM_BOOLEAN:
                return (bool) $value;

            case Table\Config::FORM_DATETIME:
                return new \DateTime($value);

            case Table\Config::FORM_TEXT:
                return $value;

            case Table\Config::FORM_EMAIL:
                return $value;

            default:
            case Table\Config::FORM_STRING:
                return $value;
        }
    }
}
