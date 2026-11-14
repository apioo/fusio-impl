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

namespace Fusio\Impl\Service\Agent\Executor;

use Fusio\Model\Agent\Schema;
use Fusio\Model\Agent\SchemaType;
use Fusio\Model\Agent\SchemaTypeProperty;

/**
 * Transforms the flat schema structure produced by the schema agent into a TypeSchema specification. This is a port of
 * the ExportService of the ngx-typeschema-editor which was previously used at the frontend
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class SchemaTransformer
{
    private const SCALAR_TYPES = ['string', 'integer', 'number', 'boolean', 'any'];

    /**
     * @return array<string, mixed>
     */
    public function transform(Schema $schema): array
    {
        $result = [];

        $types = $schema->getTypes() ?? [];
        if (count($types) > 0) {
            $definitions = [];
            foreach ($types as $type) {
                $name = $type->getName();
                if (empty($name)) {
                    continue;
                }

                if ($type->getType() === 'map') {
                    $definitions[$name] = $this->transformMap($type);
                } else {
                    $definitions[$name] = $this->transformStruct($type);
                }
            }

            $result['definitions'] = $definitions;
        }

        $root = $schema->getRoot();
        if ($root !== null && isset($types[$root])) {
            $result['root'] = $types[$root]->getName();
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformStruct(SchemaType $type): array
    {
        $result = [];
        if (!empty($type->getDescription())) {
            $result['description'] = $type->getDescription();
        }

        if ($type->getDeprecated() !== null) {
            $result['deprecated'] = $type->getDeprecated();
        }

        $result['type'] = 'struct';

        if (!empty($type->getParent())) {
            $result['parent'] = [
                'type' => 'reference',
                'target' => $type->getParent(),
            ];
        }

        $properties = $type->getProperties() ?? [];
        if (count($properties) > 0) {
            $props = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                if (empty($name)) {
                    continue;
                }

                $props[$name] = $this->transformProperty($property);
            }

            $result['properties'] = $props;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformMap(SchemaType $type): array
    {
        $result = [];
        if (!empty($type->getDescription())) {
            $result['description'] = $type->getDescription();
        }

        if ($type->getDeprecated() !== null) {
            $result['deprecated'] = $type->getDeprecated();
        }

        $result['type'] = 'map';

        $schema = $this->resolveType($type->getReference());
        if ($schema !== null) {
            $result['schema'] = $schema;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformProperty(SchemaTypeProperty $property): array
    {
        $result = [];
        if (!empty($property->getDescription())) {
            $result['description'] = $property->getDescription();
        }

        if ($property->getDeprecated() !== null) {
            $result['deprecated'] = $property->getDeprecated();
        }

        if ($property->getNullable() !== null) {
            $result['nullable'] = $property->getNullable();
        }

        $type = $property->getType();
        $reference = $property->getReference();

        if ($type === 'reference' || $type === 'object') {
            $result['type'] = 'reference';
            $result['target'] = $reference;
        } elseif ($type === 'map' && !empty($reference)) {
            $result['type'] = 'map';
            $result['schema'] = $this->resolveType($reference);
        } elseif ($type === 'array' && !empty($reference)) {
            $result['type'] = 'array';
            $result['schema'] = $this->resolveType($reference);
        } elseif (in_array($type, self::SCALAR_TYPES, true)) {
            $result['type'] = $type;
        }

        return $result;
    }

    /**
     * @return array<string, string>|null
     */
    private function resolveType(?string $reference): ?array
    {
        if (empty($reference)) {
            return null;
        }

        if (in_array($reference, self::SCALAR_TYPES, true)) {
            return ['type' => $reference];
        }

        return [
            'type' => 'reference',
            'target' => $reference,
        ];
    }
}
