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

namespace Fusio\Impl\Backend\Action\Backup;

use Fusio\Cli\Service;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Cli\Authenticator;
use Fusio\Model\Backend\BackupImport;

/**
 * Import
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Import implements ActionInterface
{
    private Service\Import $import;
    private Service\Client $client;

    public function __construct(Service\Import $import, Service\Client $client)
    {
        $this->import = $import;
        $this->client = $client;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $requestContext = $request->getContext();
        if ($requestContext instanceof HttpRequestContext) {
            $header = $requestContext->getRequest()->getHeader('Authorization');
            $parts = explode(' ', $header, 2);
            $token = $parts[1] ?? '';

            $this->client->setAuthenticator(new Authenticator('', $token));
        }

        $body = $request->getPayload();

        assert($body instanceof BackupImport);

        $generator = $this->import->import('' . $body->getImport());

        /** @var array<string> $logs */
        $logs = [];
        foreach ($generator as $result) {
            $logs[] = $result->toString();
        }

        return [
            'success' => true,
            'message' => 'Import successfully executed',
            'logs' => $logs,
        ];
    }
}
