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

namespace Fusio\Impl\Service\System;

use Fusio\Impl\Deploy\Transformer;
use Fusio\Impl\Deploy\EnvProperties;
use Fusio\Impl\Deploy\IncludeDirective;
use Fusio\Impl\Deploy\TransformerInterface;
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
    const TYPE_SERVER = 'server';

    /**
     * @var \Fusio\Impl\Service\System\Import
     */
    protected $importService;

    /**
     * @var \Fusio\Impl\Service\System\WebServer
     */
    protected $webServerService;

    /**
     * @var EnvProperties
     */
    private $envProperties;

    /**
     * @var IncludeDirective
     */
    private $includeDirective;

    /**
     * @param \Fusio\Impl\Service\System\Import $importService
     * @param \Fusio\Impl\Service\System\WebServer $webServerService
     * @param \Fusio\Impl\Deploy\EnvProperties $envProperties
     */
    public function __construct(Import $importService, WebServer $webServerService, EnvProperties $envProperties)
    {
        $this->importService    = $importService;
        $this->webServerService = $webServerService;
        $this->envProperties    = $envProperties;
        $this->includeDirective = new IncludeDirective($envProperties);
    }

    /**
     * @param string $data
     * @param string|null $basePath
     * @return \Fusio\Impl\Service\System\Import\Result
     */
    public function deploy($data, $basePath = null)
    {
        $data   = Yaml::parse($this->envProperties->replace($data), Yaml::PARSE_CUSTOM_TAGS);
        $import = new \stdClass();

        if (empty($basePath)) {
            $basePath = getcwd();
        }

        $transformers = [
            SystemAbstract::TYPE_SCOPE      => $this->newTransformer(Transformer\Scope::class),
            SystemAbstract::TYPE_USER       => $this->newTransformer(Transformer\User::class),
            SystemAbstract::TYPE_APP        => $this->newTransformer(Transformer\App::class),
            SystemAbstract::TYPE_CONFIG     => $this->newTransformer(Transformer\Config::class),
            SystemAbstract::TYPE_CONNECTION => $this->newTransformer(Transformer\Connection::class),
            SystemAbstract::TYPE_SCHEMA     => $this->newTransformer(Transformer\Schema::class),
            SystemAbstract::TYPE_ACTION     => $this->newTransformer(Transformer\Action::class),
            SystemAbstract::TYPE_ROUTES     => $this->newTransformer(Transformer\Routes::class),
            SystemAbstract::TYPE_CRONJOB    => $this->newTransformer(Transformer\Cronjob::class),
            SystemAbstract::TYPE_RATE       => $this->newTransformer(Transformer\Rate::class),
            SystemAbstract::TYPE_EVENT      => $this->newTransformer(Transformer\Event::class),
        ];

        // resolve includes
        foreach ($transformers as $type => $transformer) {
            if (isset($data[$type])) {
                $data[$type] = $this->includeDirective->resolve($data[$type], $basePath, $type);
            }
        }

        // run transformer
        foreach ($transformers as $type => $transformer) {
            /** @var TransformerInterface $transformer */
            $transformer->transform($data, $import, $basePath);
        }

        // import definition
        $result = $this->importService->import(json_encode($import));

        // web server
        $server = isset($data[self::TYPE_SERVER]) ? $data[self::TYPE_SERVER] : [];
        $server = $this->includeDirective->resolve($server, $basePath, self::TYPE_SERVER);

        if (is_array($server)) {
            $result->merge($this->webServerService->generate($server));
        }

        return $result;
    }

    private function newTransformer(string $class): TransformerInterface
    {
        return new $class($this->includeDirective);
    }
}
