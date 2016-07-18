<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use Fusio\Impl\Table\Config as TableConfig;
use Fusio\Impl\Table\Scope as TableScope;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Sql;

/**
 * Config
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Config
{
    /**
     * @var \Fusio\Impl\Table\Config
     */
    protected $configTable;

    public function __construct(TableConfig $configTable)
    {
        $this->configTable = $configTable;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        $condition = new Condition();

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        return new ResultSet(
            $this->configTable->getCount($condition),
            $startIndex,
            16,
            $this->configTable->getAll(
                $startIndex,
                16,
                'name',
                Sql::SORT_ASC,
                $condition
            )
        );
    }

    public function get($configId)
    {
        $config = $this->configTable->get($configId);

        if (!empty($config)) {
            return $config;
        } else {
            throw new StatusCode\NotFoundException('Could not find config');
        }
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
            case TableConfig::FORM_NUMBER:
                return 0 + $value;

            case TableConfig::FORM_BOOLEAN:
                return (bool) $value;

            case TableConfig::FORM_DATETIME:
                return new \DateTime($value);

            case TableConfig::FORM_TEXT:
                return $value;

            case TableConfig::FORM_EMAIL:
                return $value;

            default:
            case TableConfig::FORM_STRING:
                return $value;
        }
    }
}
