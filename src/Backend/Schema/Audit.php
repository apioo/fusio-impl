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

namespace Fusio\Impl\Backend\Schema;

use PSX\Schema\SchemaAbstract;

/**
 * Audit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Audit extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('Audit App');
        $sb->integer('id');
        $sb->integer('status');
        $sb->string('name');
        $app = $sb->getProperty();

        $sb = $this->getSchemaBuilder('Audit User');
        $sb->integer('id');
        $sb->integer('status');
        $sb->string('name');
        $user = $sb->getProperty();

        $sb = $this->getSchemaBuilder('Audit Object');
        $sb->setDescription('A key value object containing the changes');
        $sb->setAdditionalProperties(true);
        $content = $sb->getProperty();

        $sb = $this->getSchemaBuilder('Audit');
        $sb->integer('id');
        $sb->objectType('app', $app);
        $sb->objectType('user', $user);
        $sb->string('event');
        $sb->string('ip');
        $sb->string('message');
        $sb->objectType('content', $content);
        $sb->dateTime('date');

        return $sb->getProperty();
    }
}
