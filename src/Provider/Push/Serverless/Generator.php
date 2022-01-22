<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Provider\Push\Serverless;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Serverless\Config;
use Fusio\Engine\Serverless\GeneratorInterface;
use PSX\Api\Util\Inflection;
use Symfony\Component\Yaml\Yaml;

/**
 * Generator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Generator implements GeneratorInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function generate(string $basePath, Config $config, string $stub): \Generator
    {
        $targetDir = 'handler';
        $generator = $config->getHandlerGenerator();

        if (!is_dir($basePath . '/' . $targetDir)) {
            mkdir($basePath . '/' . $targetDir);
        }

        $functions = [];
        $methods = $this->getMethods();
        foreach ($methods as $method) {
            if ($generator instanceof \Closure) {
                $handler = $generator($targetDir, $method['action']);
            } else {
                $handler = $targetDir . '/' . $method['action'] . '.php';
            }

            $function = [
                'handler' => $handler,
                'events' => [
                    'http' => [
                        'path' => Inflection::convertPlaceholderToCurly($method['path']),
                        'method' => $method['method'],
                    ]
                ],
            ];

            $layers = $config->getLayers();
            if (!empty($layers)) {
                $function['layers'] = $layers;
            }

            $functions[$method['action']] = $function;

            $actionFile = $basePath . '/' . $targetDir . '/' . $method['action'] . '.php';
            $bytes = file_put_contents($actionFile, $this->generateCode($method, $stub));

            yield 'Generated handler ' . $actionFile . ' wrote ' . $bytes . ' bytes';
        }

        $yaml = [
            'provider' => [
                'name' => $config->getProviderName(),
                'runtime' => $config->getProviderRuntime(),
            ],
            'plugins' => $config->getPlugins(),
            'functions' => $functions,
        ];

        $file = $basePath . '/serverless.yaml';
        $bytes = file_put_contents($file, Yaml::dump($yaml, 8));

        yield 'Wrote serverless.yaml ' . $bytes . ' bytes';
    }

    private function generateCode(array $method, string $stub): string
    {
        $method['id'] = (int) $method['id'];
        $method['route_id'] = (int) $method['route_id'];
        $method['status'] = (int) $method['status'];
        $method['public'] = (bool) $method['public'];
        $method['category_id'] = (int) $method['category_id'];

        $return = '<?php' . "\n";
        $return.= '// Fusio handler to execute a specific route' . "\n";
        $return.= '// Automatically generated on ' . date('Y-m-d') . "\n";
        $return.= 'require __DIR__ . "/../vendor/autoload.php";' . "\n";
        $return.= '$container = require_once(__DIR__ . "/../container.php");' . "\n";
        $return.= '\PSX\Framework\Bootstrap::setupEnvironment($container->get("config"));' . "\n";
        $return.= '$method = ' . var_export($method, true) . ';' . "\n";
        $return.= "\n";
        $return.= $stub;
        $return.= "\n";

        return $return;
    }

    private function getMethods(): array
    {
        $sql = 'SELECT method.id,
                       method.route_id,
                       method.method,
                       method.status,
                       method.public,
                       method.action,
                       routes.category_id,
                       routes.path
                  FROM fusio_routes_method method
            INNER JOIN fusio_routes routes
                    ON routes.id = method.route_id
                 WHERE method.active = 1
                   AND routes.status = 1
              ORDER BY method.id DESC';

        return $this->connection->fetchAll($sql);
    }
}
