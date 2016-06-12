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

use Fusio\Impl\Model\Action;
use Fusio\Impl\Parser\ParserAbstract;
use Fusio\Impl\Processor\MemoryRepository;
use Fusio\Impl\Table\Action as TableAction;
use Fusio\Impl\Table\Routes\Method as TableRoutesMethod;
use Fusio\Impl\Table\Schema as TableSchema;
use Fusio\Impl\Form;
use PSX\Api\Resource;
use PSX\Sql\Condition;
use InvalidArgumentException;

/**
 * Deploys a route method from development to production. That means that we
 * create the schema and action cache so that the method can no longer change
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Deploy
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Impl\Table\Schema
     */
    protected $schemaTable;

    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Impl\Parser\ParserAbstract
     */
    protected $actionParser;

    public function __construct(TableRoutesMethod $routesMethodTable, TableSchema $schemaTable, TableAction $actionTable, ParserAbstract $actionParser)
    {
        $this->routesMethodTable = $routesMethodTable;
        $this->schemaTable       = $schemaTable;
        $this->actionTable       = $actionTable;
        $this->actionParser      = $actionParser;
    }

    public function deploy($method)
    {
        unset($method['id']);

        $method['status'] = Resource::STATUS_ACTIVE;

        if ($method['request'] > 0) {
            $method['requestCache'] = $this->getSchemaCache($method['request']);
        }

        if ($method['response'] > 0) {
            $method['responseCache'] = $this->getSchemaCache($method['response']);
        }

        if ($method['action'] > 0) {
            $method['actionCache'] = $this->getActionCache($method['action']);
        }

        $this->routesMethodTable->create($method);
    }
    
    protected function getSchemaCache($schemaId)
    {
        $schema = $this->schemaTable->get($schemaId);
        return $schema['cache'];
    }
    
    protected function getActionCache($actionId)
    {
        $repository = new MemoryRepository();
        $this->buildRepository($actionId, $repository);

        return serialize($repository);
    }

    protected function buildRepository($actionId, MemoryRepository $repository)
    {
        $action  = $this->actionTable->get($actionId);
        $config  = $action->config;
        $form    = $this->actionParser->getForm($action->class);

        if ($form instanceof Form\Container) {
            $elements = $form->getElements();
            foreach ($elements as $element) {
                if ($element instanceof Form\Element\Action) {
                    $name = $element->getName();
                    if (isset($config[$name]) && $config[$name] > 0) {
                        $this->buildRepository($config[$name], $repository);
                    }
                }
            }
        }

        $entry = new Action();
        $entry->setId($action['id']);
        $entry->setName($action['name']);
        $entry->setClass($action['class']);
        $entry->setConfig($action['config']);
        $entry->setDate($action['date']->format('Y-m-d H:i:s'));

        $repository->add($entry);
    }
}
