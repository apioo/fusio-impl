<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Schema;

use PSX\Schema\SchemaAbstract;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Rate extends SchemaAbstract
{
    const NAME_PATTERN = '^[a-zA-Z0-9\-\_]{3,64}$';

    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('Rate');
        $sb->integer('id');
        $sb->integer('priority')
            ->setMinimum(0);
        $sb->string('name')
            ->setPattern(self::NAME_PATTERN);
        $sb->integer('rateLimit')
            ->setMinimum(0);
        $sb->duration('timespan');
        $sb->arrayType('allocation')
            ->setItems($this->getSchema(Rate\Allocation::class));

        return $sb->getProperty();
    }
}
