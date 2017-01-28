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

namespace Fusio\Impl\Table\Log;

use PSX\Sql\Condition;
use PSX\Sql\TableAbstract;

/**
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Error extends TableAbstract
{
    public function getName()
    {
        return 'fusio_log_error';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'logId' => self::TYPE_INT,
            'message' => self::TYPE_VARCHAR,
            'trace' => self::TYPE_TEXT,
            'file' => self::TYPE_VARCHAR,
            'line' => self::TYPE_INT,
        );
    }

    public function getErrors($startIndex = 0, $search = null)
    {
        $condition  = new Condition();

        if (!empty($search)) {
            $condition->like('message', '%' . $search . '%');
        }

        $builder = $this->connection->createQueryBuilder()
            ->select(['error.id', 'error.message', 'log.path', 'log.date'])
            ->from('fusio_log_error', 'error')
            ->innerJoin('error', 'fusio_log', 'log', 'error.logId = log.id')
            ->orderBy('error.id', 'DESC')
            ->setFirstResult($startIndex)
            ->setMaxResults(16);

        if ($condition->hasCondition()) {
            $builder->where($condition->getExpression($this->connection->getDatabasePlatform()));
            $builder->setParameters($condition->getValues());
        }

        $definition = [
            'totalResults' => $this->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $this->doCollection($builder->getSQL(), $builder->getParameters(), [
                'id' => 'id',
                'message' => 'message',
                'path' => 'path',
                'date' => $this->dateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

}
