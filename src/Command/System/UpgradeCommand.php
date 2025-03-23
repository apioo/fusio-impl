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

namespace Fusio\Impl\Command\System;

use Fusio\Impl\Service\System\FrameworkConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * UpgradeCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UpgradeCommand extends Command
{
    public function __construct(private FrameworkConfig $config)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:upgrade')
            ->setAliases(['upgrade'])
            ->setDescription('Converts all YAML operation definitions to PHP');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting upgrade ...');

        $baseDir = __DIR__ . '/../../../resources/operations';
        if (!is_dir($baseDir)) {
            $output->writeln('Folder resources/operations does not exist');
            return self::FAILURE;
        }

        $this->recursiveMigrate($baseDir, $output);

        $output->writeln('Upgrade completed');

        return self::SUCCESS;
    }

    private function recursiveMigrate(string $baseDir, OutputInterface $output): void
    {
        $files = scandir($baseDir);
        foreach ($files as $file) {
            if ($file[0] === '.') {
                continue;
            }

            $path = $baseDir . '/' . $file;
            if (is_dir($path)) {
                $this->recursiveMigrate($path, $output);
            } elseif (is_file($path)) {
                $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
                if ($fileExtension !== 'yaml') {
                    continue;
                }

                $phpFile = pathinfo($path, PATHINFO_FILENAME) . '.php';
                $targetFile = $baseDir . '/' . $phpFile;

                $this->migrate($path, $targetFile);

                $output->writeln('* ' . $targetFile);
            }
        }
    }

    private function migrate(string $sourceFile, string $targetFile): void
    {
        $content = file_get_contents($sourceFile);
        if ($content === false) {
            throw new \RuntimeException('Could not read file ' . $sourceFile);
        }

        $data = Yaml::parse($sourceFile);
        $lines = [];

        if (isset($data['scopes'])) {
            $lines[] = '$operation->setScopes(' . \json_encode($data['scopes']) . ');';
        }

        if (isset($data['stability'])) {
            if ($data['stability'] === 0) {
                $lines[] = '$operation->setScopes(Stability::DEPRECATED);';
            } elseif ($data['stability'] === 1) {
                $lines[] = '$operation->setScopes(Stability::EXPERIMENTAL);';
            } elseif ($data['stability'] === 2) {
                $lines[] = '$operation->setScopes(Stability::STABLE);';
            } elseif ($data['stability'] === 3) {
                $lines[] = '$operation->setScopes(Stability::LEGACY);';
            }
        }

        if (isset($data['public'])) {
            if ($data['public'] === true) {
                $lines[] = '$operation->setPublic(true);';
            } elseif ($data['public'] === false) {
                $lines[] = '$operation->setPublic(false);';
            }
        }

        if (isset($data['description'])) {
            $lines[] = '$operation->setDescription(\'' . $data['description'] . '\');';
        }

        if (isset($data['httpMethod'])) {
            $lines[] = '$operation->setHttpMethod(HttpMethod::' . $data['httpMethod'] . ');';
        }

        if (isset($data['httpPath'])) {
            $lines[] = '$operation->setHttpPath(\'' . $data['httpPath'] . '\');';
        }

        if (isset($data['httpCode'])) {
            $lines[] = '$operation->setHttpCode(' . $data['httpCode'] . ');';
        }

        if (isset($data['parameters']) && is_array($data['parameters'])) {
            foreach ($data['parameters'] as $name => $parameter) {
                if ($parameter['type'] === 'integer') {
                    $lines[] = '$operation->addParameter(\'' . $name . '\', PropertyTypeFactory::getInteger());';
                } elseif ($parameter['type'] === 'number') {
                    $lines[] = '$operation->addParameter(\'' . $name . '\', PropertyTypeFactory::getNumber());';
                } elseif ($parameter['type'] === 'string') {
                    $lines[] = '$operation->addParameter(\'' . $name . '\', PropertyTypeFactory::getString());';
                } elseif ($parameter['type'] === 'boolean') {
                    $lines[] = '$operation->addParameter(\'' . $name . '\', PropertyTypeFactory::getBoolean());';
                }
            }
        }

        if (isset($data['incoming'])) {
            $lines[] = '$operation->setIncoming(' . substr($data['incoming'], 4) . '::class);';
        }

        if (isset($data['outgoing'])) {
            $lines[] = '$operation->setOutgoing(' . substr($data['outgoing'], 4) . '::class);';
        }

        if (isset($data['throws']) && is_array($data['throws'])) {
            foreach ($data['throws'] as $httpCode => $schema) {
                $lines[] = '$operation->addThrow(' . $httpCode . ', ' . substr($schema, 4) . '::class);';
            }
        }

        if (isset($data['action'])) {
            $lines[] = '$operation->setAction(' . substr($data['action'], 4) . '::class);';
        }

        $content = implode("\n    ", $lines);

        $code = <<<PHP
<?php

use App\Action;
use App\Model;
use Fusio\Cli\Builder\Operation;
use Fusio\Cli\Builder\Operation\HttpMethod;
use Fusio\Cli\Builder\Operation\Stability;
use PSX\Schema\Type\Factory\PropertyTypeFactory;

return function (Operation \$operation) {
    {$content}
};

PHP;

        file_put_contents($targetFile, $code);
    }
}
