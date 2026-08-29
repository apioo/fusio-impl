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

namespace Fusio\Impl\System\Action\Captcha;

use DateTime;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Environment\IPResolver;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Challenge
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Challenge implements ActionInterface
{
    public function __construct(
        private Service\Captcha $captchaService,
        private Table\Log $logTable,
        private IPResolver $ipResolver,
        private Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        if ($this->getRequestCount() > 15) {
            throw new StatusCode\TooManyRequestsException('Rate limit exceeded', 60);
        }

        return $this->captchaService->challenge();
    }

    private function getRequestCount(): int
    {
        $past = new DateTime();
        $past->sub(new \DateInterval('PT1M'));

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\LogTable::COLUMN_IP, $this->ipResolver->resolveByEnvironment());
        $condition->greater(Table\Generated\LogTable::COLUMN_DATE, $past->format('Y-m-d H:i:s'));

        return $this->logTable->getCount($condition);
    }
}
