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

namespace Fusio\Impl\Service\Event;

/**
 * SenderInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
interface SenderInterface
{
    /**
     * Returns whether the sender supports this dispatcher instance
     * 
     * @param object $dispatcher
     * @return boolean
     */
    public function accept(object $dispatcher): bool;

    /**
     * Sends an event using the dispatcher. The dispatcher is by default an http client but it also possible to
     * configure another dispatcher by using the name of an connection. Through this it would be possible to dispatch
     * events using different message queue systems. By default the sender can handle
     */
    public function send(object $dispatcher, Message $message): int;
}
