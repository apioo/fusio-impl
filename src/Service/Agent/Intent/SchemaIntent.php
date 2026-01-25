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

namespace Fusio\Impl\Service\Agent\Intent;

use Fusio\Impl\Service\Agent\IntentInterface;

/**
 * SchemaIntent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class SchemaIntent implements IntentInterface
{
    public function getMessage(): string
    {
        $hint = 'The user has the intent to develop a new schema.' . "\n";
        $hint.= 'Therefor you need generate a JSON configuration which is used to create a new schema.' . "\n";
        $hint.= 'The format of this schema is described in the provided JSON schema.' . "\n";
        $hint.= "\n";
        $hint.= 'Inside the configuration there is a source property where you need to generate a JSON TypeSchema specification.' . "\n";
        $hint.= 'You need to transform the provided user message into a TypeSchema specification.' . "\n";
        $hint.= 'The TypeSchema JSON structure is also described through the provided JSON schema.' . "\n";
        $hint.= "\n";

        return $hint;
    }

    public function getTools(): array
    {
        return [
            'backend_schema_getAll',
            'backend_schema_get',
        ];
    }

    public function getResponseFormat(): ?array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'Schema',
                'strict' => true,
                'schema' => [
                    '$defs' => [
                        'TypeSchema' => [
                            'description' => 'Describes a TypeSchema specification',
                            'type' => 'object',
                            'properties' => [
                                'definitions' => [
                                    'description' => 'A map of definition types',
                                    'type' => 'object',
                                    'additionalProperties' => [
                                        '$ref' => '#/$defs/DefinitionType',
                                    ]
                                ],
                                'root' => [
                                    'description' => 'A reference to the root type which must be a key at the definitions map',
                                    'type' => 'string',
                                ],
                            ],
                            'required' => ['definitions', 'root'],
                            'additionalProperties' => false,
                        ],
                        'PropertyType' => [
                            'anyOf' => [
                                [
                                    'description' => 'Boolean type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['boolean'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Integer type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['integer'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Number type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['number'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'String type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['string'],
                                        ],
                                    ],
                                    'required' => ['type'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Reference type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['reference'],
                                        ],
                                        'target' => [
                                            'description' => 'The reference target must be a key which is available under the definitions object',
                                            'type' => 'string',
                                        ],
                                    ],
                                    'required' => ['type', 'target'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                        'DefinitionType' => [
                            'anyOf' => [
                                [
                                    'description' => 'Struct type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['struct'],
                                        ],
                                        'properties' => [
                                            'type' => 'object',
                                            'additionalProperties' => [
                                                '$ref' => '#/$defs/PropertyType',
                                            ],
                                        ],
                                    ],
                                    'required' => ['type', 'properties'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Map type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['map'],
                                        ],
                                        'schema' => [
                                            '$ref' => '#/$defs/PropertyType',
                                        ],
                                    ],
                                    'required' => ['type', 'schema'],
                                    'additionalProperties' => false,
                                ],
                                [
                                    'description' => 'Array type',
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => [
                                            'type' => 'string',
                                            'enum' => ['array'],
                                        ],
                                        'schema' => [
                                            '$ref' => '#/$defs/PropertyType',
                                        ],
                                    ],
                                    'required' => ['type', 'schema'],
                                    'additionalProperties' => false,
                                ],
                            ],
                        ],
                    ],
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'description' => 'A short and precise name as lower case and separated by hyphens which summarizes the user message',
                            'type' => 'string',
                        ],
                        'source' => [
                            'description' => 'The TypeSchema specification',
                            '$ref' => '#/$defs/TypeSchema',
                        ],
                    ],
                    'required' => ['definitions', 'root'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}
