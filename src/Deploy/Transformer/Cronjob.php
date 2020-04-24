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

use Fusio\Impl\Deploy\NameGenerator;
use Fusio\Impl\Deploy\TransformerAbstract;
use Fusio\Impl\Service\System\SystemAbstract;

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Cronjob extends TransformerAbstract
{
    public function transform(array $data, \stdClass $import, $basePath)
    {
        $cronjob = isset($data[SystemAbstract::TYPE_CRONJOB]) ? $data[SystemAbstract::TYPE_CRONJOB] : [];

        if (!empty($cronjob) && is_array($cronjob)) {
            $result = [];
            foreach ($cronjob as $name => $entry) {
                $result[] = $this->transformCronjob($name, $entry, $basePath);
            }
            $import->cronjob = $result;
        }
    }

    protected function transformCronjob($name, $data, $basePath)
    {
        $data = $this->includeDirective->resolve($data, $basePath, SystemAbstract::TYPE_CRONJOB);
        $data['name'] = $name;
        $data['action'] = NameGenerator::getActionNameFromSource($data['action']);

        return $data;
    }
}
