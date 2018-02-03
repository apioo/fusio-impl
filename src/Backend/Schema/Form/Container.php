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

namespace Fusio\Impl\Backend\Schema\Form;

use PSX\Schema\Property;
use PSX\Schema\SchemaAbstract;

/**
 * Container
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Container extends SchemaAbstract
{
    public function getDefinition()
    {
        $sb = $this->getSchemaBuilder('container');
        $sb->arrayType('element')
            ->setItems(Property::get()->setOneOf([
                $this->getSchema(Element\Input::class),
                $this->getSchema(Element\Select::class),
                $this->getSchema(Element\Tag::class),
                $this->getSchema(Element\TextArea::class),
            ]));

        return $sb->getProperty();
    }
}
