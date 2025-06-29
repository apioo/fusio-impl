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

namespace Fusio\Impl\Service\System\Health;

/**
 * CheckResult
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CheckResult
{
    private array $checks = [];

    public function isHealthy(): bool
    {
        $count = 0;
        foreach ($this->checks as $check) {
            if ($check['healthy'] === true) {
                $count++;
            }
        }
        
        return count($this->checks) === $count;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function add(string $name, bool $healthy, ?string $error = null)
    {
        $check = [
            'healthy' => $healthy,
        ];

        if ($error !== null) {
            $check['error'] = $error;
        }

        $this->checks[$name] = $check;
    }
}
