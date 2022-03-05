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

namespace Fusio\Impl\Service;

use Fusio\Model\Backend\Sdk_Generate;
use PSX\Api\GeneratorFactory;
use PSX\Api\GeneratorFactoryInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Framework\Config\Config as FrameworkConfig;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Sdk
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Sdk
{
    private Application $console;
    private FrameworkConfig $config;

    public function __construct(Application $console, FrameworkConfig $config)
    {
        $this->console = $console;
        $this->config = $config;
    }

    public function generate(Sdk_Generate $record): string
    {
        $format = $record->getFormat();
        $config = $record->getConfig();

        if (!in_array($format, GeneratorFactory::getPossibleTypes())) {
            throw new StatusCode\BadRequestException('Invalid format provided');
        }

        $sdkDir = $this->getSdkDir();
        if (!is_dir($sdkDir)) {
            mkdir($sdkDir);
        }

        $filter = 'external';
        $file = 'sdk-' . $format . '-' . $filter . '.zip';

        $parameters = [
            'command'  => 'api:generate',
            'dir'      => $sdkDir,
            '--format' => $format,
            '--filter' => $filter,
        ];

        if (!empty($config)) {
            $parameters['--config'] = $config;
        }

        $autoExit = $this->console->isAutoExitEnabled();
        $this->console->setAutoExit(false);
        $this->console->run(new ArrayInput($parameters), new NullOutput());
        $this->console->setAutoExit($autoExit);

        return $this->config['psx_url'] . '/sdk/' . $file;
    }

    public function getTypes(): array
    {
        $sdkDir = $this->getSdkDir();
        $result = [];
        $types  = GeneratorFactory::getPossibleTypes();

        foreach ($types as $type) {
            $fileName = $this->getFileName($type);
            $sdkZip = $sdkDir . '/' . $fileName;
            if (is_file($sdkZip)) {
                $result[$type] = $this->config['psx_url'] . '/sdk/' . $fileName;
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
            case GeneratorFactoryInterface::MARKUP_HTML:
                return 'output-' . $type . '-external.html';

            case GeneratorFactoryInterface::MARKUP_MARKDOWN:
                return 'output-' . $type . '-external.md';

            case GeneratorFactoryInterface::SPEC_RAML:
                return 'output-' . $type . '-external.raml';

            case GeneratorFactoryInterface::SPEC_OPENAPI:
            case GeneratorFactoryInterface::SPEC_TYPESCHEMA:
                return 'output-' . $type . '-external.json';

            default:
                return 'sdk-' . $type . '-external.zip';
        }
    }
}
