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

namespace Fusio\Impl\Service\Adapter;

use Fusio\Engine\AdapterInterface;
use PSX\Framework\Config\ConfigInterface;

/**
 * The installer inserts only the action and connection classes through the database connection. All other entries are
 * inserted through the API endpoint
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Installer
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function install(AdapterInterface $adapter): void
    {
        $providerFile = $this->config->get('fusio_provider');
        if (!is_file($providerFile)) {
            throw new \RuntimeException('Configured provider file does not exist: ' . $providerFile);
        }

        $provider = include $providerFile;
        if (!is_array($provider)) {
            throw new \RuntimeException('Provider file ' . $providerFile . ' must return an array');
        }

        if (in_array($adapter::class, $provider)) {
            // adapter already registered
            return;
        }

        $provider[] = $adapter::class;

        $provider = array_unique($provider);
        sort($provider);

        $code = '<?php' . "\n\n" . 'return ' . var_export($provider, true) . ';';

        file_put_contents($providerFile, $code);
    }
}
