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

namespace Fusio\Impl\Service\GraphQL;

use Fusio\Engine\Request;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\Action\Invoker;
use Fusio\Impl\Service\Rate\Limiter;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Table;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use InvalidArgumentException;
use PSX\Data\WriterInterface;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\MediaType;
use PSX\Http\Response;
use PSX\Record\Record;
use PSX\Schema\Generator\Normalizer;
use PSX\Sql\Condition;
use stdClass;

/**
 * Resolver
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Resolver
{
    private Normalizer\GraphQL $normalizer;

    public function __construct(
        private Table\Operation $operationTable,
        private Invoker $invoker,
        private ContextFactory $contextFactory,
        private TokenValidator $tokenValidator,
        private Limiter $limiterService,
        private ResponseWriter $responseWriter)
    {
        $this->normalizer = new Normalizer\GraphQL();
    }

    public function resolveQuery(array $typeConfig, ?string $authorization, string $ip): array
    {
        $typeConfig['fields'] = function () use ($typeConfig, $authorization, $ip): array {
            $fields = $typeConfig['fields']();

            $context = $this->contextFactory->getActive();

            $condition = Condition::withAnd();
            $condition->equals(Table\Generated\OperationColumn::TENANT_ID, $context->getTenantId());
            $condition->equals(Table\Generated\OperationColumn::CATEGORY_ID, 1);
            $condition->equals(Table\Generated\OperationColumn::HTTP_METHOD, 'GET');
            $operations = $this->operationTable->findBy($condition);
            foreach ($operations as $operation) {
                $fields[$this->normalizer->method($operation->getName())]['resolve'] = function ($rootValue, array $args, $ctx, ResolveInfo $resolveInfo) use ($operation, $context, $authorization, $ip) {
                    $request = new Request($args, new Record(), new Request\GraphQLRequestContext($rootValue, $ctx, $resolveInfo->getFieldSelection()));

                    $context->setOperation($operation);

                    $this->tokenValidator->assertAuthorization($authorization, $context);

                    $this->limiterService->assertLimit($ip, $context->getOperation(), $context->getApp(), $context->getUser());

                    $result = $this->invoker->invoke($request, $context);

                    $response = new Response();
                    $this->responseWriter->setBody($response, $result, WriterInterface::JSON);

                    if ($this->isJson($response)) {
                        // in case the response contains JSON data we decode it
                        return json_decode((string) $response->getBody());
                    } else {
                        return [];
                    }
                };
            }

            return $fields;
        };

        return $typeConfig;
    }

    public function resolveType(array $typeConfig): array
    {
        $typeConfig['fields'] = function () use ($typeConfig): array {
            $fields = $typeConfig['fields']();

            foreach ($fields as $index => $field) {
                if ($field['type'] instanceof ObjectType) {
                    $fields[$index]['resolve'] = function ($data) use ($index) {
                        if ($data instanceof stdClass && isset($data->{$index})) {
                            return $data->{$index};
                        } elseif (is_array($data) && array_key_exists($index, $data)) {
                            return $data[$index];
                        } else {
                            return $data;
                        }
                    };
                }
            }

            return $fields;
        };

        return $typeConfig;
    }

    private function isJson(Response $response): bool
    {
        try {
            return MediaType\Json::isMediaType(MediaType::parse($response->getHeader('Content-Type')));
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
