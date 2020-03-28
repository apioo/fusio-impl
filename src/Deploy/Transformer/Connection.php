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

use Fusio\Impl\Deploy\IncludeDirective;
use Fusio\Impl\Deploy\TransformerInterface;
use Fusio\Impl\Service\System\SystemAbstract;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Connection implements TransformerInterface
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $connection = isset($data[SystemAbstract::TYPE_CONNECTION]) ? $data[SystemAbstract::TYPE_CONNECTION] : [];

        if (!empty($connection) && is_array($connection)) {
            $result = [];
            foreach ($connection as $name => $entry) {
                $result[] = $this->transformConnection($name, $entry, $basePath);
            }
            $import->connection = $result;
        }
    }

    protected function transformConnection($name, $data, $basePath)
    {
        $data = IncludeDirective::resolve($data, $basePath, SystemAbstract::TYPE_CONNECTION);
        $data['name'] = $name;

        return $data;
    }
}
