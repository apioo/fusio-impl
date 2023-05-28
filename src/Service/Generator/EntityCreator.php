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

namespace Fusio\Impl\Service\Generator;

use Fusio\Engine\Schema\SchemaName;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Action;
use Fusio\Impl\Service\Operation;
use Fusio\Impl\Service\Schema;
use Fusio\Model;
use PSX\Api\OperationInterface;

/**
 * EntityCreator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class EntityCreator
{
    private Operation $operationService;
    private Schema $schemaService;
    private Action $actionService;

    public function __construct(Operation $operationService, Schema $schemaService, Action $actionService)
    {
        $this->operationService = $operationService;
        $this->schemaService = $schemaService;
        $this->actionService = $actionService;
    }

    /**
     * @param Model\Backend\SchemaCreate[] $schemas
     */
    public function createSchemas(int $categoryId, array $schemas, string $prefix, UserContext $context): void
    {
        foreach ($schemas as $record) {
            $record->setName($this->buildName($prefix, $record->getName() ?? ''));

            $source = $record->getSource();
            if (isset($source['$import']) && is_iterable($source['$import'])) {
                $import = [];
                foreach ($source['$import'] as $name => $schema) {
                    if (str_starts_with($schema, 'schema:///')) {
                        $import[$name] = 'schema:///' . $prefix . '_' . substr($schema, 10);
                    } else {
                        $import[$name] = $schema;
                    }
                }
                $source['$import'] = $import;
                $record->setSource($source);
            }

            $id = $this->schemaService->exists($record->getName() ?? '');
            if (!$id) {
                $this->schemaService->create($categoryId, $record, $context);
            }
        }
    }

    /**
     * @param Model\Backend\ActionCreate[] $actions
     */
    public function createActions(int $categoryId, array $actions, string $prefix, UserContext $context): void
    {
        foreach ($actions as $record) {
            $record->setName($this->buildName($prefix, $record->getName() ?? ''));

            $id = $this->actionService->exists($record->getName() ?? '');
            if (!$id) {
                $this->actionService->create($categoryId, $record, $context);
            }
        }
    }

    /**
     * @param Model\Backend\OperationCreate[] $operations
     */
    public function createOperations(int $categoryId, array $operations, $scopes, ?bool $public, string $basePath, string $prefix, UserContext $context): void
    {
        $scopes = $scopes ?: [];
        $reservedSchemaNames = [SchemaName::PASSTHRU, SchemaName::MESSAGE];

        foreach ($operations as $record) {
            $record->setActive(true);
            $record->setPublic($public ?? false);
            $record->setStability(OperationInterface::STABILITY_EXPERIMENTAL);
            $record->setName($this->buildName($prefix, $record->getName() ?? '', '.', false));
            $record->setScopes(array_unique(array_merge($scopes, $record->getScopes() ?? [])));

            $path = '/' . implode('/', array_filter(explode('/', $basePath . '/' . $record->getHttpPath())));
            $record->setHttpPath($path);

            $incoming = $record->getIncoming();
            if (!empty($incoming) && !in_array($incoming, $reservedSchemaNames)) {
                $record->setIncoming($this->buildName($prefix, $incoming));
            }

            $outgoing = $record->getOutgoing();
            if (!in_array($outgoing, $reservedSchemaNames)) {
                $record->setOutgoing($this->buildName($prefix, $outgoing));
            }

            $throws = $record->getThrows();
            if (!empty($throws)) {
                $result = [];
                foreach ($throws as $code => $throw) {
                    if (!in_array($throw, $reservedSchemaNames)) {
                        $result[$code] = $this->buildName($prefix, $throw);
                    } else {
                        $result[$code] = $throw;
                    }
                }
                $record->setThrows(Model\Backend\OperationThrows::fromArray($result));
            }

            $record->setAction($this->buildName($prefix, $record->getAction()));

            $id = $this->operationService->exists((string) $record->getName());
            if (!$id) {
                $this->operationService->create($categoryId, $record, $context);
            }
        }
    }

    private function buildName(string $prefix, string $name, string $separator = '_', bool $pascalCase = true): string
    {
        $parts = explode('_', $prefix . '_' . $name);
        $parts = array_filter($parts, function ($value) {
            return $value !== '';
        });
        if ($pascalCase) {
            $parts = array_map('ucfirst', $parts);
        } else {
            $parts = array_map('lcfirst', $parts);
        }
        $parts = implode($separator, $parts);
        return $parts;
    }
}
