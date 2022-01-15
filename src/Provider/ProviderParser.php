<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Factory\FactoryInterface;
use Fusio\Engine\Form;
use Fusio\Engine\Parser\ParserAbstract;

/**
 * ProviderParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderParser extends ParserAbstract
{
    private ProviderLoader $providerLoader;
    private string $type;
    private string $instanceOf;

    public function __construct(FactoryInterface $factory, Form\ElementFactoryInterface $elementFactory, ProviderLoader $providerLoader, string $type, string $instanceOf)
    {
        parent::__construct($factory, $elementFactory);

        $this->providerLoader = $providerLoader;
        $this->type           = $type;
        $this->instanceOf     = $instanceOf;
    }

    public function getClasses(): array
    {
        $classes = $this->providerLoader->getConfig()->getClasses($this->type);
        $result  = [];

        foreach ($classes as $class) {
            $object     = $this->getObject($class);
            $instanceOf = $this->instanceOf;

            if ($object instanceof $instanceOf) {
                $result[] = [
                    'name'  => $object->getName(),
                    'class' => $class,
                ];
            }
        }

        usort($result, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $result;
    }
}
