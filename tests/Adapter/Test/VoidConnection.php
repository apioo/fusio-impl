<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\Connection\DeploymentInterface;
use Fusio\Engine\Connection\LifecycleInterface;
use Fusio\Engine\ConnectionAbstract;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;

/**
 * VoidConnection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class VoidConnection extends ConnectionAbstract implements DeploymentInterface, LifecycleInterface
{
    public function getName(): string
    {
        return 'Void-Connection';
    }

    public function getConnection(ParametersInterface $config): mixed
    {
        return new \stdClass();
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newInput('foo', 'Foo', 'text', 'Description'));
    }

    public function onUp(string $name, ParametersInterface $config): void
    {
    }

    public function onDown(string $name, ParametersInterface $config): void
    {
    }

    public function onCreate(string $name, ParametersInterface $config, mixed $connection): void
    {
    }

    public function onUpdate(string $name, ParametersInterface $config, mixed $connection): void
    {
    }

    public function onDelete(string $name, ParametersInterface $config, mixed $connection): void
    {
    }
}
