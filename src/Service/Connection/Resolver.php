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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Resolver
{
    public const TYPE_MAILER = 'mailer';
    public const TYPE_DISPATCHER = 'dispatcher';

    private Connector $connector;
    private Config $configService;

    public function __construct(Connector $connector, Config $configService)
    {
        $this->connector     = $connector;
        $this->configService = $configService;
    }

    /**
     * Returns a configured connection from the provided type
     */
    public function get(string $type): mixed
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
