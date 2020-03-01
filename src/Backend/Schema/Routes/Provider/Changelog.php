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

namespace Fusio\Impl\Backend\Schema\Routes\Provider;

use Fusio\Impl\Backend\Schema\Action;
use Fusio\Impl\Backend\Schema\Routes;
use Fusio\Impl\Backend\Schema\Schema;
use PSX\Schema\SchemaAbstract;

/**
 * Changelog
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Changelog extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('Routes Provider Changelog');
        $sb->arrayType('schemas')->setItems($this->getSchema(Schema::class));
        $sb->arrayType('actions')->setItems($this->getSchema(Action::class));
        $sb->arrayType('routes')->setItems($this->getSchema(Routes::class));

        return $sb->getProperty();
    }
}
