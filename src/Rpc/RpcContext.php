<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Rpc;

use PSX\Http\Environment\HttpContextInterface;

/**
 * RpcContext
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class RpcContext implements HttpContextInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param string $method
     * @param array $arguments
     */
    public function __construct(string $method, array $arguments)
    {
        $this->method    = $method;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getHeader($name)
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders()
    {
        return $this->arguments;
    }

    /**
     * @inheritdoc
     */
    public function getUriFragment($name)
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getUriFragments()
    {
        return $this->arguments;
    }

    /**
     * @inheritdoc
     */
    public function getParameter($name)
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getParameters()
    {
        return $this->arguments;
    }
}
