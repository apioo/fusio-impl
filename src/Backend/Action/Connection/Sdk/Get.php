<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Connection\Sdk;

use Fusio\Engine\Connector;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Schema\SchemaManagerInterface;
use stdClass;
use TypeAPI\Editor;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Get extends SdkAbstract
{
    public function __construct(private SchemaManagerInterface $schemaManager, Connector $connector, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($connector, $frameworkConfig);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $this->assertConnectionEnabled();

        $connection = $this->getConnection($request);

        $reflection = new \ReflectionClass($connection);

        $lockFile = dirname($reflection->getFileName()) . '/../sdkgen.lock';
        if (!is_file($lockFile)) {
            throw new StatusCode\InternalServerErrorException('Found no lock file');
        }

        $content = file_get_contents($lockFile);
        if ($content === false) {
            throw new StatusCode\InternalServerErrorException('Could not read lock file');
        }

        $data = Parser::decode($content);
        if (!$data instanceof stdClass) {
            throw new StatusCode\InternalServerErrorException('Could not read lock file');
        }

        $specification = $this->getFirst($data);
        if (!$specification instanceof stdClass) {
            throw new StatusCode\InternalServerErrorException('Found no specification');
        }

        return (new Editor\Parser($this->schemaManager))->parse($specification);
    }

    private function getFirst(stdClass $data)
    {
        foreach (get_object_vars($data) as $value) {
            return $value;
        }

        return null;
    }
}
