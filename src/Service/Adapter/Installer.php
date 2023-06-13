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

namespace Fusio\Impl\Service\Adapter;

use Fusio\Engine\AdapterInterface;
use PSX\Framework\Config\ConfigInterface;

/**
 * The installer inserts only the action and connection classes through the database connection. All other entries are
 * inserted through the API endpoint
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
