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

namespace Fusio\Impl\Backend\Filter;

use Fusio\Engine\RequestInterface;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * QueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class QueryFilter
{
    public const COLUMN_SEARCH = 'search';

    private int $startIndex;
    private int $count;
    private ?string $search;
    private ?string $sortBy;
    private ?OrderBy $sortOrder;

    public function __construct(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if ($startIndex <= 0) {
            $startIndex = 0;
        }

        if ($count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortOrder === 'asc') {
            $sortOrder = OrderBy::ASC;
        } elseif ($sortOrder === 'desc') {
            $sortOrder = OrderBy::DESC;
        } else {
            $sortOrder = null;
        }

        $this->startIndex = $startIndex;
        $this->count = $count;
        $this->search = $search;
        $this->sortBy = $sortBy;
        $this->sortOrder = $sortOrder;
    }

    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getSortBy(string $default): ?string
    {
        return $this->sortBy ?? $default;
    }

    public function getSortOrder(OrderBy $default): OrderBy
    {
        return $this->sortOrder ?? $default;
    }

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = Condition::withAnd();
        $alias = $this->getAlias($alias);

        if (isset($columnMapping[self::COLUMN_SEARCH]) && !empty($this->search)) {
            $condition->like($alias . $columnMapping[self::COLUMN_SEARCH], '%' . $this->search . '%');
        }

        return $condition;
    }

    protected function getAlias(?string $alias): string
    {
        return $alias !== null ? $alias . '.' : '';
    }

    public static function from(RequestInterface $request): static
    {
        /** @phpstan-ignore new.static */
        return new static(...static::getConstructorArguments($request));
    }

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        return [
            'startIndex' => (int) $request->get('startIndex'),
            'count' => (int) $request->get('count'),
            'search' => $request->get('search'),
            'sortBy' => $request->get('sortBy'),
            'sortOrder' => $request->get('sortOrder'),
        ];
    }
}
