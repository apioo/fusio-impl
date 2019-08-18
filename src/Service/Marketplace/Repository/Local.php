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

namespace Fusio\Impl\Service\Marketplace\Repository;

use Fusio\Impl\Service\Marketplace\App;
use Fusio\Impl\Service\Marketplace\RepositoryInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Local
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Local implements RepositoryInterface
{
    /**
     * @var string
     */
    private $publicPath;

    /**
     * @param string $publicPath
     */
    public function __construct(string $publicPath)
    {
        $this->publicPath = $publicPath;
    }

    /**
     * @inheritDoc
     */
    public function fetch(): array
    {
        $apps = scandir($this->publicPath);
        $result = [];

        foreach ($apps as $name) {
            $path = $this->publicPath . '/' . $name . '/app.yaml';

            if (is_file($path)) {
                $data = Yaml::parse(file_get_contents($path));

                $result[$name] = App::fromArray($name, $data);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function fetchByName(string $name): ?App
    {
        $apps = $this->fetch();

        return $apps[$name] ?? null;
    }
}
