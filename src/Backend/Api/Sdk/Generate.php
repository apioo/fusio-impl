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

namespace Fusio\Impl\Backend\Api\Sdk;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use PSX\Api\GeneratorFactory;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as Statuscode;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.sdk']);
        $get->addResponse(200, Model\Sdk_Types::class);

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::BACKEND, ['backend.sdk']);
        $post->setRequest(Model\Sdk_Generate::class);
        $post->addResponse(200, Model\Message::class);

        return $builder->getSpecification();
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
     * @param Model\Sdk_Generate $record
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->console->setAutoExit(false);

        $format = $record->getFormat();
        $config = $record->getConfig();

        if (!in_array($format, GeneratorFactory::getPossibleTypes())) {
            throw new StatusCode\BadRequestException('Invalid format provided');
        }

        $sdkDir = $this->getSdkDir();
        if (!is_dir($sdkDir)) {
            mkdir($sdkDir);
        }

        $file = 'sdk-' . $format . '.zip';

        $this->generate($sdkDir, $format, $config);

        return [
            'success' => true,
            'message' => 'SDK successfully generated',
            'link' => $this->config['psx_url'] . '/sdk/' . $file,
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

    private function getSdkDir()
    {
        return $this->config->get('psx_path_public') . '/sdk';
    }
}
