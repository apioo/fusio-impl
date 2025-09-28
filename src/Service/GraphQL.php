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

namespace Fusio\Impl\Service;

use Exception;
use Fusio\Impl\Service\GraphQL\Resolver;
use Fusio\Impl\Service\System\FrameworkConfig;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Language\Parser;
use GraphQL\Server\Helper;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;
use GraphQL\Validator\DocumentValidator;
use PSX\Api\GeneratorFactory;
use PSX\Api\Repository\LocalRepository;
use PSX\Api\Scanner\FilterFactoryInterface;
use PSX\Api\ScannerInterface;

/**
 * GraphQL
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GraphQL
{
    private Helper $helper;

    public function __construct(
        private Resolver $resolver,
        private FrameworkConfig $frameworkConfig,
        private ScannerInterface $scanner,
        private GeneratorFactory $factory,
        private FilterFactoryInterface $filterFactory,
    )
    {
        $this->helper = new Helper();
    }

    /**
     * @throws RequestError
     * @throws InvariantViolation
     * @throws Exception
     */
    public function run(string $method, array $bodyParams, array $queryParams, ?string $authorization, string $ip): mixed
    {
        $data = $this->helper->parseRequestParams($method, $bodyParams, $queryParams);

        $cacheFilename = $this->frameworkConfig->getPathCache('graphql_schema.php');
        if (!file_exists($cacheFilename)) {
            $schema = $this->generateSchema();

            $document = Parser::parse($schema);
            DocumentValidator::assertValidSDL($document);
            file_put_contents($cacheFilename, "<?php\nreturn " . var_export(AST::toArray($document), true) . ";\n");
        } else {
            $document = AST::fromArray(require $cacheFilename);
        }

        $typeConfigDecorator = function (array $typeConfig) use ($authorization, $ip) {
            if ($typeConfig['name'] === 'Query') {
                return $this->resolver->resolveQuery($typeConfig, $authorization, $ip);
            } else {
                return $this->resolver->resolveType($typeConfig);
            }
        };

        /** @psalm-suppress ImplicitToStringCast */
        $schema = BuildSchema::build($document, $typeConfigDecorator, ['assumeValidSDL' => true]);

        $config = ServerConfig::create();
        $config->setSchema($schema);

        return (new StandardServer($config))->executeRequest($data);
    }

    private function generateSchema(): string
    {
        // @TODO get from request
        $filterName = $this->filterFactory->getDefault();

        $filter = null;
        if (!empty($filterName)) {
            $filter = $this->filterFactory->getFilter($filterName);
            if ($filter === null) {
                throw new \RuntimeException('Provided an invalid filter name');
            }
        }

        $registry = $this->factory->factory();
        $generator = $registry->getGenerator(LocalRepository::SPEC_GRAPHQL);

        $spec = $generator->generate($this->scanner->generate($filter));

        return (string) $spec;
    }
}
