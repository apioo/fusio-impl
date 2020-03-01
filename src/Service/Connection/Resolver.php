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

namespace Fusio\Impl\Service\Connection;

use Fusio\Engine\Connector;
use Fusio\Impl\Service\Config;

/**
 * The connection resolver is used by the internal system to get specific
 * configured connections which can also be changed by the user through the
 * config. I.e. by default we send events through HTTP but it would be also
 * possible to pass those events into a message queue
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Resolver
{
    const TYPE_MAILER = 'mailer';
    const TYPE_DISPATCHER = 'dispatcher';

    /**
     * @var \Fusio\Engine\Connector
     */
    protected $connector;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @param \Fusio\Engine\Connector $connector
     * @param \Fusio\Impl\Service\Config $configService
     */
    public function __construct(Connector $connector, Config $configService)
    {
        $this->connector     = $connector;
        $this->configService = $configService;
    }

    /**
     * Returns a configured connection from the provided type
     *
     * @param string $type
     * @return mixed|null
     */
    public function get($type)
    {
        if ($type === self::TYPE_MAILER) {
            $name = $this->configService->getValue('system_mailer');
        } elseif ($type === self::TYPE_DISPATCHER) {
            $name = $this->configService->getValue('system_dispatcher');
        } else {
            throw new \InvalidArgumentException('Provided type does not exist');
        }

        if (empty($name)) {
            return null;
        }

        return $this->connector->getConnection($name);
    }
}
