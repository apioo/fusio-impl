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

namespace Fusio\Impl\Backend\Api\Sdk;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use PSX\Api\GeneratorFactory;
use PSX\Api\Resource;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as Statuscode;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Generate extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Symfony\Component\Console\Application
     */
    protected $console;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.sdk'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Sdk\Types::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend.sdk'])
            ->setRequest($this->schemaManager->getSchema(Schema\Sdk\Generate::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritDoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return [
            'types' => $this->getTypes(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->console->setAutoExit(false);

        $format = $record->format;
        $config = $record->config;

        if (!in_array($format, GeneratorFactory::getPossibleTypes())) {
            throw new StatusCode\BadRequestException('Invalid format provided');
        }

        $sdkDir = $this->getSdkDir();
        if (!is_dir($sdkDir)) {
            mkdir($sdkDir);
        }

        $folderName = 'sdk-' . $format;
        $sdk = $sdkDir . '/' . $folderName;

        if (!is_dir($sdk)) {
            mkdir($sdk);
        }

        $this->generate($sdk, $format, $config);

        $sdkZip = $sdk . '.zip';

        $this->createZip($sdkZip, $sdk);
        $this->moveToTrash($sdk, $folderName);

        return [
            'success' => true,
            'message' => 'SDK successfully generated',
            'link' => $this->config['psx_url'] . '/sdk/' . $folderName . '.zip',
        ];
    }

    private function getTypes(): array
    {
        $sdkDir = $this->getSdkDir();
        $result = [];
        $types  = GeneratorFactory::getPossibleTypes();
        
        foreach ($types as $type) {
            $fileName = 'sdk-' . $type . '.zip';
            $sdkZip = $sdkDir . '/' . $fileName;
            if (is_file($sdkZip)) {
                $result[$type] = $this->config['psx_url'] . '/sdk/' . $fileName;
            } else {
                $result[$type] = null;
            }
        }

        return $result;
    }

    private function generate($dir, $format, $config)
    {
        $parameters = [
            'command'  => 'api:generate',
            'dir'      => $dir,
            '--format' => $format,
            '--filter' => 'external',
        ];

        if (!empty($config)) {
            $parameters['--config'] = $config;
        }

        $this->console->run(new ArrayInput($parameters), new NullOutput());
    }

    private function createZip($zipFile, $sdkDir)
    {
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $dir = new \RecursiveDirectoryIterator($sdkDir);
        foreach ($dir as $path => $file) {
            /** @var \SplFileInfo $file */
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }

            if ($file->isFile()) {
                $relativePath = substr($path, strlen($sdkDir) + 1);
                $zip->addFile($path, $relativePath);
            }
        }

        $zip->close();
    }

    private function moveToTrash($dir, $folderName)
    {
        (new Filesystem())->rename($dir, $this->config->get('psx_path_cache') . '/' . $folderName . '-' . uniqid());
    }

    private function getSdkDir()
    {
        return $this->config->get('psx_path_public') . '/sdk';
    }
}
