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

namespace Fusio\Impl\Backend\Action\Specification;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use Fusio\Model\Backend\SpecificationChangelog;
use PSX\Api\Exception\PublishException;
use PSX\Api\TypeHub\PublisherInterface;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;

/**
 * Tag
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Tag implements ActionInterface
{
    public function __construct(private PublisherInterface $publisher, private Service\Config $config)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $clientId = $this->config->getString('typehub_client_id');
        $clientSecret = $this->config->getString('typehub_client_secret');
        if (empty($clientId) || empty($clientSecret)) {
            throw new BadRequestException('TypeHub credentials not configured, in order to push your specification to TypeHub you need to register an account at typehub.cloud and configure the credentials at System / Config (typehub_client_id/typehub_client_secret)');
        }

        $name = $this->config->getString('typehub_document_name');
        if (empty($name)) {
            throw new BadRequestException('TypeHub document name not configured, please provide a TypeHub document at System / Config (typehub_document_name) under which the specification gets published');
        }

        try {
            $this->publisher->tag($name, $clientId, $clientSecret);
        } catch (PublishException $e) {
            throw new InternalServerErrorException($e->getMessage(), previous: $e);
        }

        return [
            'success' => true,
            'message' => 'Specification tag successfully created',
        ];
    }
}
