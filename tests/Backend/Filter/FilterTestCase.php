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

namespace Fusio\Impl\Tests\Backend\Filter;

use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use PHPUnit\Framework\TestCase;
use PSX\Http\Request;
use PSX\Record\Record;
use PSX\Uri\Uri;

/**
 * FilterTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class FilterTestCase extends TestCase
{
    protected function createRequest(array $parameters): RequestInterface
    {
        $uri = Uri::parse('/');
        $uri = $uri->withParameters($parameters);

        return new \Fusio\Engine\Request($parameters, new Record(), new HttpRequestContext(new Request($uri, 'GET'), []));
    }
}
