<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
                    'class' => $object::class,
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
            } elseif (strcasecmp(NameBuilder::fromClass($object::class), $name) === 0) {
                return $object;
            }
        }

        throw new InvalidProviderException($name);
    }
}
