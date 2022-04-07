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

namespace Fusio\Impl\Service\Marketplace\Repository;

use Fusio\Impl\Service\Marketplace\App;
use Fusio\Impl\Service\Marketplace\RepositoryInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Local
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Local implements RepositoryInterface
{
    private string $appsPath;
    private array $apps;

    public function __construct(string $appsPath)
    {
        $this->appsPath = $appsPath;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(): array
    {
        if (!$this->apps) {
            $this->apps = $this->scanDir();
        }

        return $this->apps;
    }

    /**
     * @inheritDoc
     */
    public function fetchByName(string $name): ?App
    {
        $apps = $this->fetchAll();

        return $apps[$name] ?? null;
    }

    private function scanDir(): array
    {
        if (!is_dir($this->appsPath)) {
            return [];
        }

        $apps = scandir($this->appsPath);
        $result = [];

        foreach ($apps as $name) {
            $path = $this->appsPath . '/' . $name . '/app.yaml';

            if (is_file($path)) {
                $data = Yaml::parse(file_get_contents($path));

                $result[$name] = App::fromArray($name, $data);
            }
        }

        return $result;
    }
}
