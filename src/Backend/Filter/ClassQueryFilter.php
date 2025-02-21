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

namespace Fusio\Impl\Backend\Filter;

use Fusio\Engine\RequestInterface;
use PSX\Sql\Condition;

/**
 * DateQueryFilter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ClassQueryFilter extends QueryFilter
{
    public const COLUMN_CLASS = 'class';

    private array $class;

    public function __construct(array $class, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        parent::__construct($startIndex, $count, $search, $sortBy, $sortOrder);

        $this->class = $class;
    }

    public function getClass(): array
    {
        return $this->class;
    }

    public function getCondition(array $columnMapping, ?string $alias = null): Condition
    {
        $condition = parent::getCondition($columnMapping, $alias);
        $alias = $this->getAlias($alias);

        if (isset($columnMapping[self::COLUMN_CLASS]) && count($this->class) > 0) {
            $condition->in($alias . $columnMapping[self::COLUMN_CLASS], $this->class);
        }

        return $condition;
    }

    protected static function getConstructorArguments(RequestInterface $request): array
    {
        $arguments = parent::getConstructorArguments($request);

        $rawClass = $request->get('class');
        if (!empty($rawClass)) {
            $class = array_filter(explode(',', $rawClass));
        } else {
            $class = [];
        }

        $arguments['class'] = $class;

        return $arguments;
    }
}
