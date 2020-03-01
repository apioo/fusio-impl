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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\Connection\DeploymentInterface;
use Fusio\Engine\Connection\LifecycleInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;

/**
 * VoidConnection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class VoidConnection implements ConnectionInterface, DeploymentInterface, LifecycleInterface
{
    public function getName()
    {
        return 'Void-Connection';
    }

    public function getConnection(ParametersInterface $config)
    {
        return new \stdClass();
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('foo', 'Foo', 'text', 'Description'));
    }

    public function onUp($name, ParametersInterface $config)
    {
    }

    public function onDown($name, ParametersInterface $config)
    {
    }

    public function onCreate($name, ParametersInterface $config, $connection)
    {
    }

    public function onUpdate($name, ParametersInterface $config, $connection)
    {
    }

    public function onDelete($name, ParametersInterface $config, $connection)
    {
    }
}
