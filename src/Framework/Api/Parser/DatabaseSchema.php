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

namespace Fusio\Impl\Framework\Api\Parser;

use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Service\Operation\SpecificationBuilder;
use PSX\Api\Parser\Attribute;
use PSX\Api\ParserInterface;
use PSX\Api\Specification;
use PSX\Api\SpecificationInterface;
use PSX\Framework\Loader\RoutingParserInterface;

/**
 * DatabaseSchema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class DatabaseSchema implements ParserInterface
{
    private SpecificationBuilder $builder;
    private Attribute $attributeParser;

    public function __construct(SpecificationBuilder $builder, Attribute $attributeParser)
    {
        $this->builder = $builder;
        $this->attributeParser = $attributeParser;
    }

    public function parse(string $schema): SpecificationInterface
    {
        if (str_starts_with($schema, 'operation:')) {
            return $this->builder->build(substr($schema, 10));
        } else {
            return $this->attributeParser->parse($schema);
        }
    }
}
