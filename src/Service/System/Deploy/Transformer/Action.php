<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System\Deploy\Transformer;

use Fusio\Impl\Service\System\Deploy\IncludeDirective;
use Fusio\Impl\Service\System\Deploy\NameGenerator;
use Fusio\Impl\Service\System\Deploy\TransformerInterface;
use Fusio\Impl\Service\System\SystemAbstract;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Action implements TransformerInterface
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $resolvedActions = $this->resolveActionsFromRoutes($data);

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
        $data = IncludeDirective::resolve($data, $basePath, SystemAbstract::TYPE_ACTION);
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
    private function resolveActionsFromRoutes(array $data)
    {
        $actions = [];
        $type    = SystemAbstract::TYPE_ROUTES;

        if (isset($data[$type]) && is_array($data[$type])) {
            foreach ($data[$type] as $name => $row) {
                if (isset($row['methods']) && is_array($row['methods'])) {
                    foreach ($row['methods'] as $method => $config) {
                        if (isset($config['action']) && !preg_match('/' . NameGenerator::NAME_REGEXP . '/', $config['action'])) {
                            $name = NameGenerator::getActionNameFromSource($config['action']);

                            $actions[$name] = [
                                'class' => $config['action']
                            ];
                        }
                    }
                }
            }
        }

        return $actions;
    }
}
