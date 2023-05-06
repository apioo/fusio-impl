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

namespace Fusio\Impl\Framework\Loader\RoutingParser;

use PSX\Api\Scanner\FilterInterface;
use PSX\Framework\Loader\RoutingCollection;
use PSX\Framework\Loader\RoutingParser\AttributeParser;
use PSX\Framework\Loader\RoutingParserInterface;

/**
 * CompositeParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CompositeParser implements RoutingParserInterface
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

        foreach ($this->attributeParser->getCollection($filter) as $row) {
            $collection->add(...$row);
        }

        return $this->collection[$key] = $collection;
    }
}
