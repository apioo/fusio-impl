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

namespace Fusio\Impl\Service;

use Fusio\Model\Backend\SdkGenerate;
use PSX\Api\GeneratorFactory;
use PSX\Api\Repository\LocalRepository;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Sdk
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Sdk
{
    private Application $console;
    private GeneratorFactory $factory;
    private ConfigInterface $config;

    public function __construct(Application $console, GeneratorFactory $factory, ConfigInterface $config)
    {
        $this->console = $console;
        $this->factory = $factory;
        $this->config = $config;
    }

    public function generate(SdkGenerate $record): string
    {
        $registry = $this->factory->factory();

        $format = $record->getFormat();
        $config = $record->getConfig();

        if (!in_array($format, $registry->getPossibleTypes())) {
            throw new StatusCode\BadRequestException('Invalid format provided');
        }

        $sdkDir = $this->getSdkDir();
        if (!is_dir($sdkDir)) {
            mkdir($sdkDir);
        }

        $filter = 'default';
        $file = 'sdk-' . $format . '-' . $filter . '.zip';

        $parameters = [
            'command'  => 'generate:sdk',
            'type'     => $format,
            '--filter' => $filter,
            '--output' => $sdkDir,
        ];

        if (!empty($config)) {
            $parameters['--config'] = $config;
        }

        $autoExit = $this->console->isAutoExitEnabled();
        $this->console->setAutoExit(false);
        $this->console->run(new ArrayInput($parameters), new NullOutput());
        $this->console->setAutoExit($autoExit);

        return $this->config->get('psx_url') . '/sdk/' . $file;
    }

    public function getTypes(): array
    {
        $registry = $this->factory->factory();

        $sdkDir = $this->getSdkDir();
        $result = [];
        $types  = $registry->getPossibleTypes();

        foreach ($types as $type) {
            $fileName = $this->getFileName($type);
            $sdkZip = $sdkDir . '/' . $fileName;
            if (is_file($sdkZip)) {
                $result[$type] = $this->config->get('psx_url') . '/sdk/' . $fileName;
            } else {
                $result[$type] = null;
            }
        }

        return $result;
    }

    private function getSdkDir(): string
    {
        return $this->config->get('psx_path_public') . '/sdk';
    }

    private function getFileName(string $type): string
    {
        switch ($type) {
            case LocalRepository::MARKUP_HTML:
                return 'output-' . $type . '-external.html';

            case LocalRepository::MARKUP_MARKDOWN:
            case LocalRepository::MARKUP_CLIENT:
                return 'output-' . $type . '-external.md';

            case LocalRepository::SPEC_OPENAPI:
            case LocalRepository::SPEC_TYPEAPI:
                return 'output-' . $type . '-external.json';

            default:
                return 'sdk-' . $type . '-external.zip';
        }
    }
}
