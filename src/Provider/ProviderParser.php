<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Provider;

use Fusio\Engine\ConfigurableInterface;
use Fusio\Engine\Factory\FactoryInterface;
use Fusio\Engine\Form;
use Fusio\Engine\NameBuilder;
use Fusio\Engine\Parser\ParserAbstract;

/**
 * ProviderParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class ProviderParser extends ParserAbstract
{
    private iterable $objects;

    public function __construct(FactoryInterface $factory, Form\ElementFactoryInterface $elementFactory, iterable $objects)
    {
        parent::__construct($factory, $elementFactory);

        $this->objects = $objects;
    }

    public function getClasses(): array
    {
        $result = [];
        foreach ($this->objects as $object) {
            if ($object instanceof ConfigurableInterface) {
                $result[] = [
                    'name'  => $object->getName(),
                    'class' => $this->serialize($object::class),
                ];
            }
        }

        usort($result, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $result;
    }

    public function getInstance(string $name): ?object
    {
        foreach ($this->objects as $object) {
            if ($object::class === $name) {
                return $object;
            } elseif ($this->serialize($object::class) === $name) {
                return $object;
            } elseif (strcasecmp($this->shortName($object::class), $name) === 0) {
                return $object;
            }
        }

        throw new InvalidProviderException($name);
    }

    private function serialize(string $class): string
    {
        return str_replace('\\', '.', $class);
    }

    private function shortName(string $class): string
    {
        return (new \ReflectionClass($class))->getShortName();
    }
}
