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

namespace Fusio\Impl\Action;

use Doctrine\DBAL\Connection;
use Doctrine\Common\Cache;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\RequestInterface;

/**
 * CacheResponse
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CacheResponse implements ActionInterface
{
    /**
     * @Inject
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @Inject
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @Inject
     * @var \Fusio\Engine\ProcessorInterface
     */
    protected $processor;

    public function getName()
    {
        return 'Cache-Response';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $key = md5($configuration->get('action') . json_encode($request->getUriFragments()) . json_encode($request->getParameters()));

        $handler  = $this->getCacheHandler($this->connector->getConnection($configuration->get('connection')));
        $response = $handler->fetch($key);

        if ($response === false) {
            $response = $this->processor->execute($configuration->get('action'), $request, $context);

            $handler->save($key, $response, $configuration->get('expire'));
        }

        return $response;
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newConnection('connection', 'Connection', 'Connection to a memcache or redis server'));
        $builder->add($elementFactory->newAction('action', 'Action', 'The response of this action is cached'));
        $builder->add($elementFactory->newInput('expire', 'Expire', 'number', 'Number of seconds when the cache expires. 0 means infinite cache lifetime.'));
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @param mixed $connection
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    protected function getCacheHandler($connection)
    {
        if ($connection instanceof \Memcache) {
            $handler = new Cache\MemcacheCache();
            $handler->setMemcache($connection);
        } elseif ($connection instanceof \Redis) {
            $handler = new Cache\RedisCache();
            $handler->setRedis($connection);
        } else {
            $handler = new Cache\ArrayCache();
        }

        return $handler;
    }
}
