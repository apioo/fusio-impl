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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Psr\Http\Message\StreamInterface;
use PSX\Data\Body\Form;
use PSX\Data\Body\Json;
use PSX\Data\Body\Multipart;

/**
 * MimeAction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MimeAction extends ActionAbstract
{
    public function getName(): string
    {
        return 'MIME-Action';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $payload = $request->getPayload();
        if ($payload instanceof StreamInterface) {
            $class = ClassName::serialize($payload::class);
            $raw = (string) $payload;
        } elseif ($payload instanceof \DOMDocument) {
            $class = ClassName::serialize($payload::class);
            $raw = $payload->saveXML();
        } elseif ($payload instanceof Form) {
            $class = ClassName::serialize($payload::class);
            $raw = $payload;
        } elseif ($payload instanceof Json) {
            $class = ClassName::serialize($payload::class);
            $raw = $payload;
        } elseif ($payload instanceof Multipart) {
            $class = ClassName::serialize($payload::class);
            $raw = $payload->getAll();
        } elseif (is_string($payload)) {
            $class = 'string';
            $raw = $payload;
        } else {
            $class = get_debug_type($payload);
            $raw = null;
        }

        return $this->response->build(200, [], [
            'class' => $class,
            'raw' => $raw
        ]);
    }
}
