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

namespace Fusio\Impl\Backend\Schema;

use PSX\Schema\Property;
use PSX\Schema\SchemaAbstract;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('methods');
        $sb->addPatternProperty('^(GET|POST|PUT|PATCH|DELETE)$', $this->getSchema(Routes\Method::class));
        $methods = $sb->getProperty();

        $sb = $this->getSchemaBuilder('version');
        $sb->integer('version');
        $sb->integer('status');
        $sb->arrayType('scopes')
            ->setItems(Property::getString());
        $sb->objectType('methods', $methods);
        $version = $sb->getProperty();

        $sb = $this->getSchemaBuilder('routes');
        $sb->integer('id');
        $sb->string('path');
        $sb->arrayType('config')
            ->setItems($version);

        return $sb->getProperty();
    }
}
