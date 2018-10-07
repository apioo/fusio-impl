<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\System;

use Fusio\Impl\Service\System\Push\ZipBuilder;
use Fusio\Impl\Service\System\Push\ZipUpload;
use PSX\Framework\Config\Config;

/**
 * Push
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Push
{
    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @var \Fusio\Impl\Service\System\Push\ZipBuilder
     */
    protected $zipBuilder;

    /**
     * @var \Fusio\Impl\Service\System\Push\ZipUpload
     */
    protected $zipUpload;

    /**
     * @param \PSX\Framework\Config\Config $config
     * @param \Fusio\Impl\Service\System\Push\ZipBuilder $zipBuilder
     * @param \Fusio\Impl\Service\System\Push\ZipUpload $zipUpload
     */
    public function __construct(Config $config, ZipBuilder $zipBuilder, ZipUpload $zipUpload)
    {
        $this->config     = $config;
        $this->zipBuilder = $zipBuilder;
        $this->zipUpload  = $zipUpload;
    }

    /**
     * @param string $basePath
     * @return \Generator
     */
    public function push($basePath)
    {
        if (!is_dir($basePath)) {
            throw new \RuntimeException('Base path is not a folder');
        }

        if (!is_file($basePath . '/.fusio.yml')) {
            throw new \RuntimeException('Looks like path is not a valid Fusio folder');
        }

        $host  = $this->config->get('fusio_provider_host');
        $key   = $this->config->get('fusio_provider_key');

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
