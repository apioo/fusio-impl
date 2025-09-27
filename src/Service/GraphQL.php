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
use Fusio\Impl\Service\GraphQL\TypeConfigDecorator;
use Fusio\Impl\Service\System\FrameworkConfig;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\Parser;
use GraphQL\Server\Helper;
use GraphQL\Server\RequestError;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;
use GraphQL\Validator\DocumentValidator;

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
        private TypeConfigDecorator $typeConfigDecorator,
        private FrameworkConfig $frameworkConfig,
    )
    {
        $this->helper = new Helper();
    }

    /**
     * @throws RequestError
     * @throws InvariantViolation
     * @throws Exception
     */
    public function run(string $method, array $bodyParams, array $queryParams): array
    {
        $data = $this->helper->parseRequestParams($method, $bodyParams, $queryParams);

        $cacheFilename = $this->frameworkConfig->getPathCache('graphql_schema.php');
        if (!file_exists($cacheFilename)) {
            $schema = ''; // @TODO build schema

            $document = Parser::parse($schema);
            DocumentValidator::assertValidSDL($document);
            file_put_contents($cacheFilename, "<?php\nreturn " . var_export(AST::toArray($document), true) . ";\n");
        } else {
            $document = AST::fromArray(require $cacheFilename);
        }

        $schema = BuildSchema::build($document, $this->typeConfigDecorator, ['assumeValidSDL' => true]);

        $config = ServerConfig::create();
        $config->setSchema($schema);

        $server = new StandardServer($config);
        $result = $server->executeRequest($data);

        return $result;
    }
}
