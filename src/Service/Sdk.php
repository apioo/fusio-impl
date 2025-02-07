<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service;

use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Model\Backend\SdkGenerate;
use PSX\Api\GeneratorFactory;
use PSX\Api\Repository\LocalRepository;
use PSX\Api\Scanner\FilterFactoryInterface;
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
readonly class Sdk
{
    public function __construct(
        private Application $console,
        private GeneratorFactory $factory,
        private FrameworkConfig $frameworkConfig,
        private FilterFactoryInterface $filterFactory
    ) {
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

        $filter = $this->filterFactory->getDefault();
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
        $catchExceptions = $this->console->areExceptionsCaught();
        $this->console->setAutoExit(false);
        $this->console->setCatchExceptions(false);

        try {
            $this->console->run(new ArrayInput($parameters), new NullOutput());
        } catch (\Throwable $e) {
            throw new StatusCode\InternalServerErrorException('Could not generate SDK: ' . $e->getMessage(), $e);
        } finally {
            $this->console->setAutoExit($autoExit);
            $this->console->setCatchExceptions($catchExceptions);
        }

        return $this->frameworkConfig->getUrl('sdk', $file);
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
                $result[$type] = $this->frameworkConfig->getUrl('sdk', $fileName);
            } else {
                $result[$type] = null;
            }
        }

        return $result;
    }

    private function getSdkDir(): string
    {
        return $this->frameworkConfig->getPathPublic('sdk');
    }

    private function getFileName(string $type): string
    {
        switch ($type) {
            case LocalRepository::MARKUP_HTML:
                return 'output-' . $type . '-app.html';

            case LocalRepository::MARKUP_MARKDOWN:
            case LocalRepository::MARKUP_CLIENT:
                return 'output-' . $type . '-app.md';

            case LocalRepository::SPEC_OPENAPI:
            case LocalRepository::SPEC_TYPEAPI:
                return 'output-' . $type . '-app.json';

            default:
                return 'sdk-' . $type . '-app.zip';
        }
    }
}
