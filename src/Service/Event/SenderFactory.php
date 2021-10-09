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

namespace Fusio\Impl\Service\Event;

use PSX\Data\Util\PriorityQueue;

/**
 * SenderFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SenderFactory
{
    /**
     * @var \Fusio\Impl\Service\Event\SenderInterface[] 
     */
    private $senders;

    public function __construct()
    {
        $this->senders = new PriorityQueue();
    }

    /**
     * @param \Fusio\Impl\Service\Event\SenderInterface $sender
     * @param integer $priority
     */
    public function add(SenderInterface $sender, $priority)
    {
        $this->senders->insert($sender, $priority);
    }

    /**
     * @param mixed $dispatcher
     * @return \Fusio\Impl\Service\Event\SenderInterface|null
     */
    public function factory($dispatcher)
    {
        foreach ($this->senders as $sender) {
            if ($sender->accept($dispatcher)) {
                return $sender;
            }
        }

        return null;
    }
}

