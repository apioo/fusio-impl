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

namespace Fusio\Impl\Backend\Filter;

use Fusio\Engine\RequestInterface;
use PSX\Sql\Condition;

/**
 * QueryFilterAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DateQueryFilter extends QueryFilter
{
    public const COLUMN_DATE = 'date';

    private \DateTimeImmutable $from;
    private \DateTimeImmutable $to;

    public function __construct(\DateTimeImmutable $from, \DateTimeImmutable $to, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($startIndex, $count, $search, $sortBy, $sortOrder);

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

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

        if (isset($columnMapping[self::COLUMN_DATE])) {
            $condition->greaterThan($alias . $columnMapping[self::COLUMN_DATE], $this->from->format('Y-m-d 00:00:00'));
            $condition->lessThan($alias . $columnMapping[self::COLUMN_DATE], $this->to->format('Y-m-d 23:59:59'));
        }

        return $condition;
    }

    protected static function toInt(mixed $value): ?int
    {
        return !empty($value) ? (int) $value : null;
    }

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $from = new \DateTimeImmutable($request->get('from') ?? '-1 month');
        $to = new \DateTimeImmutable($request->get('to') ?? 'now');

        // from date is large then to date
        if ($from->getTimestamp() > $to->getTimestamp()) {
            $tmp = clone $from;
            $from = $to;
            $to = $tmp;
        }

        // check if diff between from and to is larger than ca 2 months
        if (($to->getTimestamp() - $from->getTimestamp()) > 4838400) {
            $to = $from->add(new \DateInterval('P2M'));
        }

        $arguments['from'] = $from;
        $arguments['to'] = $to;

        return $arguments;
    }
}
