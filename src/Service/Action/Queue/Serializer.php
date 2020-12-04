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

namespace Fusio\Impl\Service\Action\Queue;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\RequestInterface;

/**
 * Serializer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Serializer
{
    public static function serializeRequest(RequestInterface $request): string
    {
        return serialize($request);
    }

    public static function unserializeRequest(string $data): RequestInterface
    {
        return unserialize($data);
    }

    public static function serializeContext(ContextInterface $context): string
    {
        return serialize($context);
    }

    public static function unserializeContext(string $data): ContextInterface
    {
        return unserialize($data);
    }
}
