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

use Fusio\Impl\Table\Action as TableAction;
use Fusio\Impl\Table\Routes;
use Fusio\Impl\Table\Routes\Action as RoutesAction;
use Fusio\Impl\Table\Routes\Method as RoutesMethod;
use Fusio\Impl\Table\Routes\Schema as RoutesSchema;
use Fusio\Impl\Form;
use Fusio\Impl\Parser\ParserAbstract;
use PSX\Api\Resource;
use PSX\Sql\Condition;

/**
 * The dependency manager inserts all schema and action ids which are used by
 * a route. Because of that we can i.e. show all actions which are used by a 
 * route
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DependencyManager
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Schema
     */
    protected $schemaLinkTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Action
     */
    protected $actionLinkTable;

    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Impl\Parser\ParserAbstract
     */
    protected $actionParser;

    public function __construct(RoutesMethod $methodTable, RoutesSchema $schemaLinkTable, RoutesAction $actionLinkTable, TableAction $actionTable, ParserAbstract $actionParser)
    {
        $this->methodTable     = $methodTable;
        $this->schemaLinkTable = $schemaLinkTable;
        $this->actionLinkTable = $actionLinkTable;
        $this->actionTable     = $actionTable;
        $this->actionParser    = $actionParser;
    }

    /**
     * Removes all existing dependency links
     *
     * @param integer $routeId
     */
    public function removeExistingDependencyLinks($routeId)
    {
        $this->schemaLinkTable->deleteAllFromRoute($routeId);
        $this->actionLinkTable->deleteAllFromRoute($routeId);
    }

    /**
     * Reads all dependencies of the rpovided route and writes them to the 
     * tables
     *
     * @param integer $routeId
     * @param array $config
     */
    public function updateDependencyLinks($routeId)
    {
        // remove all existing entries
        $this->removeExistingDependencyLinks($routeId);

        // get dependencies of the config
        $methods = $this->methodTable->getByRouteId($routeId);
        $schemas = $this->getDependingSchemas($methods);
        $actions = $this->getDependingActions($methods);

        foreach ($schemas as $schemaId => $status) {
            $this->schemaLinkTable->create(array(
                'routeId'  => $routeId,
                'schemaId' => $schemaId,
                'status'   => $status,
            ));
        }

        foreach ($actions as $actionId => $status) {
            $this->actionLinkTable->create(array(
                'routeId'  => $routeId,
                'actionId' => $actionId,
                'status'   => $status,
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
            if ($row['active']) {
                if (is_int($row['request'])) {
                    $schemaIds[] = $row['request'];
                }

                if (is_int($row['response'])) {
                    $schemaIds[] = $row['response'];
                }
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
            if ($row['active']) {
                if (is_int($row['action'])) {
                    $actionIds[] = $row['action'];
                }
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
