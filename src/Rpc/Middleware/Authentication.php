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

namespace Fusio\Impl\Rpc\Middleware;

use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;
use PSX\Record\RecordInterface;

/**
 * Authentication
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Authentication
{
    /**
     * @var Service\Security\TokenValidator
     */
    private $tokenValidator;

    /**
     * @var string
     */
    private $authorization;

    public function __construct(Service\Security\TokenValidator $tokenValidator, string $authorization)
    {
        $this->tokenValidator = $tokenValidator;
        $this->authorization = $authorization;
    }

    public function __invoke(RecordInterface $arguments, array $method, Context $context)
    {
        $success = $this->tokenValidator->assertAuthorization(
            $method['method'],
            $this->authorization,
            $context
        );

        if (!$success) {
            throw new StatusCode\UnauthorizedException('Could not authorize request', 'Bearer');
        }
    }
}