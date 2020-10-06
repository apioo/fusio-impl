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

namespace Fusio\Impl\Deploy\Transformer;

use Fusio\Impl\Backend;
use Fusio\Impl\Deploy\NameGenerator;
use Fusio\Impl\Deploy\TransformerAbstract;
use Fusio\Impl\Service\System\SystemAbstract;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Action extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $resolvedActions = $this->resolveActionsFromRoutes($data, $basePath);

        $action = isset($data[SystemAbstract::TYPE_ACTION]) ? $data[SystemAbstract::TYPE_ACTION] : [];

        if (!empty($resolvedActions)) {
            if (is_array($action)) {
                $action = array_merge($action, $resolvedActions);
            } else {
                $action = $resolvedActions;
            }
        }

        if (!empty($action) && is_array($action)) {
            $result = [];
            foreach ($action as $name => $entry) {
                $result[] = $this->transformAction($name, $entry, $basePath);
            }
            $import->action = $result;
        }
    }

    protected function transformAction($name, $data, $basePath)
    {
        $data = $this->includeDirective->resolve($data, $basePath, SystemAbstract::TYPE_ACTION);
        $data['name'] = $name;

        return $data;
    }

    /**
     * In case the routes contains a class as action we automatically create a
     * fitting action entry
     *
     * @param array $data
     * @return array
     */
    private function resolveActionsFromRoutes(array $data, $basePath)
    {
        $actions = [];
        $type    = SystemAbstract::TYPE_ROUTE;

        if (isset($data[$type]) && is_array($data[$type])) {
            foreach ($data[$type] as $name => $row) {
                // resolve includes
                $row = $this->includeDirective->resolve($row, $basePath, $type);

                if (isset($row['methods']) && is_array($row['methods'])) {
                    foreach ($row['methods'] as $method => $config) {
                        // action
                        if (isset($config['action']) && !$this->isName($config['action'])) {
                            $name = NameGenerator::getActionNameFromSource($config['action']);

                            $actions[$name] = [
                                'class'  => $config['action'],
                                'config' => new \stdClass()
                            ];
                        }
                    }
                }
            }
        }

        return $actions;
    }

    private function isName($schema)
    {
        return is_string($schema) && preg_match('/^[a-zA-Z0-9\-\_]{3,255}$/', $schema);
    }
}
