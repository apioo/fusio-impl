<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Schema;

use Fusio\Impl\Service;
use PSX\Schema\Parser\TypeSchema;

/**
 * Parser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Parser
{
    /**
     * @var TypeSchema\ImportResolver
     */
    private $importResolver;

    public function __construct(TypeSchema\ImportResolver $importResolver)
    {
        $this->importResolver = $importResolver;
    }

    /**
     * Parses and resolves the json schema source and returns the object
     * presentation of the schema
     *
     * @param string $name
     * @param string|null $source
     * @return string
     */
    public function parse(string $name, ?string $source): ?string
    {
        if (empty($source)) {
            return null;
        }

        $parser = new TypeSchema($this->importResolver);
        $schema = $parser->parse($this->transform($source, $name));

        return Service\Schema::serializeCache($schema);
    }

    private function transform(string $schema, string $name): string
    {
        $schema = json_decode($schema);
        if (!$schema instanceof \stdClass) {
            throw new \InvalidArgumentException('Schema must be of type object');
        }

        $root = [];
        foreach ($schema as $key => $value) {
            if (in_array($key, ['$import', 'definitions'])) {
                continue;
            }

            $root[$key] = $value;
            unset($schema->{$key});
        }

        if (empty($schema->definitions)) {
            $schema->definitions = new \stdClass();
        }

        $schema->definitions->{$name} = (object) $root;
        $schema->{'$ref'} = $name;

        return json_encode($schema, JSON_PRETTY_PRINT);
    }
}
