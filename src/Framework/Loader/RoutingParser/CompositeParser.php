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

namespace Fusio\Impl\Framework\Loader\RoutingParser;

use PSX\Api\Scanner\FilterInterface;
use PSX\Framework\Loader\RoutingCollection;
use PSX\Framework\Loader\RoutingParser\AttributeParser;
use PSX\Framework\Loader\RoutingParser\InvalidateableInterface;
use PSX\Framework\Loader\RoutingParserInterface;

/**
 * CompositeParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CompositeParser implements RoutingParserInterface, InvalidateableInterface
{
    private array $collection = [];
    private DatabaseParser $databaseParser;
    private AttributeParser $attributeParser;

    public function __construct(DatabaseParser $databaseParser, AttributeParser $attributeParser)
    {
        $this->databaseParser = $databaseParser;
        $this->attributeParser = $attributeParser;
    }

    public function getCollection(?FilterInterface $filter = null): RoutingCollection
    {
        $key = $filter !== null ? $filter->getId() : '0';

        if (isset($this->collection[$key])) {
            return $this->collection[$key];
        }

        $collection = new RoutingCollection();

        foreach ($this->databaseParser->getCollection($filter) as $row) {
            $collection->add(...$row);
        }

        if ($filter === null) {
            // we add the manual routes only if we have no filter
            foreach ($this->attributeParser->getCollection($filter) as $row) {
                $collection->add(...$row);
            }
        }

        return $this->collection[$key] = $collection;
    }

    public function invalidate(?FilterInterface $filter = null): void
    {
        $key = $filter !== null ? $filter->getId() : '0';

        if (isset($this->collection[$key])) {
            unset($this->collection[$key]);
        }
    }
}
