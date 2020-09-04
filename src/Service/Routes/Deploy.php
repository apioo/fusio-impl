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

namespace Fusio\Impl\Service\Routes;

use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\Resource;

/**
 * Deploys a route method from development to production. That means that we
 * create the schema and action cache so that the method can no longer change
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Deploy
{
    /**
     * @var \Fusio\Impl\Table\Routes\Method
     */
    protected $methodTable;

    /**
     * @var \Fusio\Impl\Table\Routes\Response
     */
    protected $responseTable;

    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @param \Fusio\Impl\Table\Routes\Method $methodTable
     * @param \Fusio\Impl\Table\Routes\Response $responseTable
     * @param \Fusio\Impl\Table\Action $actionTable
     */
    public function __construct(Table\Routes\Method $methodTable, Table\Routes\Response $responseTable, Table\Action $actionTable)
    {
        $this->methodTable   = $methodTable;
        $this->responseTable = $responseTable;
        $this->actionTable   = $actionTable;
    }

    public function deploy($method)
    {
        $action = null;
        if ($method['action'] > 0) {
            $action = $this->getActionCache($method['action']);
        }

        // create cache and change status
        $method['status']       = Resource::STATUS_ACTIVE;
        $method['action_cache'] = json_encode($action);

        $this->methodTable->update($method);
    }

    private function getActionCache($actionId)
    {
        $repository = new Repository\ActionMemory();
        $action     = $this->actionTable->get($actionId);

        if (!empty($action)) {
            $config = Service\Action::unserializeConfig($action['config']);

            $entry = new Model\Action();
            $entry->setId($action['id']);
            $entry->setName($action['name']);
            $entry->setClass($action['class']);
            $entry->setEngine($action['engine']);
            $entry->setConfig($config ?: []);
            $entry->setDate($action['date']->format('Y-m-d H:i:s'));

            $repository->add($entry);
        }

        return $repository->jsonSerialize();
    }
}
