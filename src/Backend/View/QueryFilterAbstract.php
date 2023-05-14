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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\RequestInterface;
use PSX\Sql\Condition;

/**
 * QueryFilterAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
abstract class QueryFilterAbstract
{
    protected \DateTimeImmutable $from;
    protected \DateTimeImmutable $to;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): \DateTimeImmutable
    {
        return $this->to;
    }

    public function getCondition(?string $alias = null): Condition
    {
        $alias     = $alias !== null ? $alias . '.' : '';
        $condition = Condition::withAnd();
        $condition->greaterThan($alias . $this->getDateColumn(), $this->from->format('Y-m-d 00:00:00'));
        $condition->lessThan($alias . $this->getDateColumn(), $this->to->format('Y-m-d 23:59:59'));

        return $condition;
    }

    protected function getDateColumn(): string
    {
        return 'date';
    }

    protected static function getFromAndTo(RequestInterface $request): array
    {
        $from = new \DateTimeImmutable($request->get('from') ?? '-1 month');
        $to   = new \DateTimeImmutable($request->get('to') ?? 'now');

        // from date is large then to date
        if ($from->getTimestamp() > $to->getTimestamp()) {
            $tmp  = clone $from;
            $from = $to;
            $to   = $tmp;
        }

        // check if diff between from and to is larger than ca 2 months
        if (($to->getTimestamp() - $from->getTimestamp()) > 4838400) {
            $to = $from->add(new \DateInterval('P2M'));
        }

        return [$from, $to];
    }

    protected static function toInt(mixed $value): ?int
    {
        return !empty($value) ? (int) $value : null;
    }
}
