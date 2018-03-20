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

use Fusio\Impl\Service\System\Import\Result;
use PSX\Framework\Config\Config;

/**
 * Class which scans every app folder and tries to replace specific environment
 * variables
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class FileScanner
{
    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $server
     * @return \Fusio\Impl\Service\System\Import\Result
     */
    public function scan()
    {
        $result = new Result();
        $path   = $this->config->get('fusio_path_apps');

        if (!empty($path) && is_dir($path)) {
            $this->scanApps($path, $result);
        }

        return $result;
    }

    private function scanApps($path, Result $result)
    {
        $apps = scandir($path);

        foreach ($apps as $app) {
            $appDir = $path . '/' . $app;
            if (is_dir($appDir)) {
                $this->scanAppFiles($appDir, $result);
            }
        }
    }

    private function scanAppFiles($appPath, Result $result)
    {
        $files = scandir($appPath);

        foreach ($files as $file) {
            if (in_array($file, ['index.html', 'index.htm'])) {
                $index = $appPath . '/' . $file;
                $count = 0;

                if (is_file($index) && is_writable($index)) {
                    $content = file_get_contents($index);
                    $content = $this->replace($content, $count);

                    if ($count > 0) {
                        $bytes = file_put_contents($index, $content);

                        if ($bytes) {
                            $result->add(Deploy::TYPE_FILE, Result::ACTION_REPLACED, 'Environment variables at ' . $file);
                        }
                    }
                }
            }
        }
    }

    private function replace($content, &$replaced)
    {
        $envs = [
            'FUSIO_URL' => $this->config->get('psx_url'),
        ];

        foreach ($envs as $key => $value) {
            $content = str_replace('${' . $key . '}', $value, $content, $count);
            $replaced+= $count;
        }

        return $content;
    }
}
