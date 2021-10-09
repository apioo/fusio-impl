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

namespace Fusio\Impl\Provider\Push;

use Fusio\Engine\Push\ProviderInterface;
use Fusio\Impl\Provider\Push\Zip\ZipBuilder;
use Fusio\Impl\Provider\Push\Zip\ZipUpload;
use PSX\Framework\Config\Config;

/**
 * Apioo
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Apioo implements ProviderInterface
{
    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @var \Fusio\Impl\Provider\Push\Zip\ZipBuilder
     */
    protected $zipBuilder;

    /**
     * @var \Fusio\Impl\Provider\Push\Zip\ZipUpload
     */
    protected $zipUpload;

    /**
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config     = $config;
        $this->zipBuilder = new ZipBuilder();
        $this->zipUpload  = new ZipUpload();
    }

    /**
     * @param string $basePath
     * @return \Generator
     */
    public function push(string $basePath)
    {
        $host = $this->config->get('fusio_provider_host');
        $key  = $this->config->get('fusio_provider_key');

        if (!empty($host) && !empty($key)) {
            // create zip
            $file = $this->config->get('psx_path_cache') . '/fusio-push-' . time() . '.zip';
            yield from $this->zipBuilder->buildZip($file, $basePath);

            // upload zip
            yield from $this->zipUpload->uploadZip($file, $host, $key);
        } else {
            throw new \RuntimeException('Provider "fusio_provider_host" and "fusio_provider_key" not defined in configuration');
        }
    }
}
