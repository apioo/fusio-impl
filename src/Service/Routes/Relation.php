<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Service\Routes;

use Fusio\Impl\Form;
use Fusio\Impl\Parser\ParserAbstract;
use Fusio\Impl\Table\Action as TableAction;
use Fusio\Impl\Table\Routes as TableRoutes;
use Fusio\Impl\Table\Routes\Action as TableRoutesAction;
use Fusio\Impl\Table\Routes\Method as TableRoutesMethod;
use Fusio\Impl\Table\Routes\Schema as TableRoutesSchema;
use PSX\Api\Resource;
use PSX\Sql\Condition;

/**
 * The relation service determines all schema and action ids which are used by a
 * route and inserts them into a table
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Relation
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Schema
     */
    protected $routesSchemaTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Action
     */
    protected $routesActionTable;

    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Impl\Parser\ParserAbstract
     */
    protected $actionParser;

    public function __construct(TableRoutesMethod $routesMethodTable, TableRoutesSchema $routesSchemaTable, TableRoutesAction $routesActionTable, TableAction $actionTable, ParserAbstract $actionParser)
    {
        $this->routesMethodTable = $routesMethodTable;
        $this->routesSchemaTable = $routesSchemaTable;
        $this->routesActionTable = $routesActionTable;
        $this->actionTable       = $actionTable;
        $this->actionParser      = $actionParser;
    }

    /**
     * Removes all existing dependency links
     *
     * @param integer $routeId
     */
    public function removeExistingRelations($routeId)
    {
        $this->routesSchemaTable->deleteAllFromRoute($routeId);
        $this->routesActionTable->deleteAllFromRoute($routeId);
    }

    /**
     * Reads all relations of the provided route and writes them to the tables
     *
     * @param integer $routeId
     */
    public function updateRelations($routeId)
    {
        // remove all existing entries
        $this->removeExistingRelations($routeId);

        // get dependencies of the config
        $methods = $this->routesMethodTable->getMethods($routeId);
        $schemas = $this->getDependingSchemas($methods);
        $actions = $this->getDependingActions($methods);

        foreach ($schemas as $schemaId) {
            $this->routesSchemaTable->create(array(
                'routeId'  => $routeId,
                'schemaId' => $schemaId,
            ));
        }

        foreach ($actions as $actionId) {
            $this->routesActionTable->create(array(
                'routeId'  => $routeId,
                'actionId' => $actionId,
            ));
        }
    }

    /**
     * Returns all schema ids which are required by the config
     *
     * @param array $methods
     * @return array
     */
    protected function getDependingSchemas(array $methods)
    {
        $schemaIds = [];
        foreach ($methods as $row) {
            if ($row['request'] > 0) {
                $schemaIds[] = $row['request'];
            }

            if ($row['response'] > 0) {
                $schemaIds[] = $row['response'];
            }
        }

        // @TODO it would be great to resolve also schemas which are referenced
        // by the provided schemas i.e. through $ref: schema://

        return array_unique($schemaIds);
    }

    /**
     * Returns all action ids which are required by the config. Resolves also
     * action ids which depend on another action
     *
     * @param array $methods
     * @return array
     */
    protected function getDependingActions(array $methods)
    {
        $actionIds = [];
        foreach ($methods as $row) {
            if ($row['action'] > 0) {
                $actionIds[] = $row['action'];
            }
        }

        return $this->resolveDependingActions($actionIds);
    }

    protected function resolveDependingActions(array $actions)
    {
        $result = [];
        foreach ($actions as $actionId) {
            // add self action
            $result[] = $actionId;

            // add depending actions
            $dependingActions = $this->getDependingActionsByAction($actionId);
            if (count($dependingActions) > 0) {
                foreach ($dependingActions as $depActionId) {
                    $result[] = $depActionId;
                }
            }
        }

        return array_unique($result);
    }

    protected function getDependingActionsByAction($actionId)
    {
        $action  = $this->actionTable->get($actionId);
        $config  = $action->config;
        $form    = $this->actionParser->getForm($action->class);
        $actions = [];

        if ($form instanceof Form\Container) {
            $elements = $form->getElements();
            foreach ($elements as $element) {
                if ($element instanceof Form\Element\Action) {
                    $name = $element->getName();
                    if (isset($config[$name]) && is_int($config[$name])) {
                        $actions[] = $config[$name];

                        $actions = array_merge($actions, $this->getDependingActionsByAction($config[$name]));
                    }
                }
            }
        }

        return array_unique($actions);
    }
}
