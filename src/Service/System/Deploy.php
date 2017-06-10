<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service\System\Deploy\IncludeDirective;
use Fusio\Impl\Service\System\Deploy\TransformerInterface;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * The deploy service basically transforms a deploy yaml config into a json 
 * format which is then used by the import service. Also it handles the 
 * database migration
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Deploy
{
    const TYPE_MIGRATION = 'migration';

    /**
     * @var \Fusio\Impl\Service\System\Import
     */
    protected $importService;

    /**
     * @var \Fusio\Impl\Service\System\Migration
     */
    protected $migrationService;

    /**
     * @param \Fusio\Impl\Service\System\Import $importService
     * @param \Fusio\Impl\Service\System\Migration $migrationService
     */
    public function __construct(Import $importService, Migration $migrationService)
    {
        $this->importService    = $importService;
        $this->migrationService = $migrationService;
    }

    /**
     * @param array $data
     * @param string|null $basePath
     * @return array
     */
    public function deploy($data, $basePath = null)
    {
        $data   = Yaml::parse($this->replaceProperties($data));
        $import = new \stdClass();

        if (empty($basePath)) {
            $basePath = getcwd();
        }

        $transformers = [
            SystemAbstract::TYPE_CONFIG     => new Deploy\Transformer\Config(),
            SystemAbstract::TYPE_CONNECTION => new Deploy\Transformer\Connection(),
            SystemAbstract::TYPE_SCHEMA     => new Deploy\Transformer\Schema(),
            SystemAbstract::TYPE_ACTION     => new Deploy\Transformer\Action(),
            SystemAbstract::TYPE_ROUTES     => new Deploy\Transformer\Routes(),
        ];

        foreach ($transformers as $type => $transformer) {
            /** @var TransformerInterface $transformer */
            $transformer->transform($data, $import, $basePath);
        }

        // import definition
        $json = json_encode($import);
        $log  = $this->importService->import($json);

        // migration
        $migration = isset($data[self::TYPE_MIGRATION]) ? $data[self::TYPE_MIGRATION] : [];
        $migration = IncludeDirective::resolve($migration, $basePath, self::TYPE_MIGRATION);

        $log = array_merge($log, $this->migrationService->execute($migration, $basePath));

        return $log;
    }

    private function replaceProperties($data)
    {
        $vars = [];
        
        // dir properties
        $vars['dir'] = [
            'cache' => PSX_PATH_CACHE,
            'src'   => PSX_PATH_LIBRARY,
            'temp'  => sys_get_temp_dir(),
        ];

        // env properties
        $vars['env'] = [];
        foreach ($_SERVER as $key => $value) {
            if (is_scalar($value)) {
                $vars['env'][strtolower($key)] = $value;
            }
        }

        foreach ($vars as $type => $properties) {
            $search  = [];
            $replace = [];
            foreach ($properties as $key => $value) {
                $search[]  = '${' . $type . '.' . $key . '}';
                $replace[] = is_string($value) ? trim(json_encode($value), '"') : $value;
            }

            $data = str_replace($search, $replace, $data);

            // check whether we have variables which were not replaced
            preg_match('/\$\{' . $type . '\.([0-9A-z_]+)\}/', $data, $matches);
            if (isset($matches[0])) {
                throw new RuntimeException('Usage of unknown property ' . $matches[0]);
            }
        }

        return $data;
    }
}
