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

namespace Fusio\Impl\Provider;

use Fusio\Engine\Factory\FactoryInterface;
use Fusio\Engine\Form;
use Fusio\Engine\Parser\ParserAbstract;

/**
 * ProviderParser
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderParser extends ParserAbstract
{
    /**
     * @var \Fusio\Impl\Provider\ProviderLoader
     */
    protected $providerLoader;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $instanceOf;

    /**
     * @param \Fusio\Engine\Factory\FactoryInterface $factory
     * @param \Fusio\Engine\Form\ElementFactoryInterface $elementFactory
     * @param \Fusio\Impl\Provider\ProviderLoader $providerLoader
     * @param string $type
     * @param string $instanceOf
     */
    public function __construct(FactoryInterface $factory, Form\ElementFactoryInterface $elementFactory, ProviderLoader $providerLoader, string $type, string $instanceOf)
    {
        parent::__construct($factory, $elementFactory);

        $this->providerLoader = $providerLoader;
        $this->type           = $type;
        $this->instanceOf     = $instanceOf;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        $classes = $this->providerLoader->getConfig()->getClasses($this->type);
        $result  = array();

        foreach ($classes as $name => $class) {
            $object     = $this->getObject($class);
            $instanceOf = $this->instanceOf;

            if ($object instanceof $instanceOf) {
                $result[] = array(
                    'name'  => $object->getName(),
                    'class' => $class,
                );
            }
        }

        return $result;
    }
}
