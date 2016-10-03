<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Connection;

use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Pheanstalk\Pheanstalk;
use RuntimeException;

/**
 * Memcache
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Memcache implements ConnectionInterface
{
    public function getName()
    {
        return 'Memcache';
    }

    /**
     * @param \Fusio\Engine\ParametersInterface $config
     * @return \Memcache
     */
    public function getConnection(ParametersInterface $config)
    {
        if (class_exists('Memcached')) {
            $memcache = new \Memcached();
            $memcache->addServer($config->get('host'), $config->get('port') ?: 11211);
        } elseif (class_exists('Memcache')) {
            $memcache = new \Memcache();
            $memcache->connect($config->get('host'), $config->get('port') ?: 11211);
        } else {
            throw new RuntimeException('PHP extension "memcached" or "memcache" is not installed');
        }

        return $memcache;
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('host', 'Host', 'text', 'The IP or hostname of the Memcache server'));
        $builder->add($elementFactory->newInput('port', 'Port', 'number', 'Port of the Memcache server default is 11211'));
    }
}
