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

namespace Fusio\Impl\Tests\Framework\Api\TypeHub;

use PSX\Api\TypeHub\Changelog;
use PSX\Api\TypeHub\PublisherInterface;
use PSX\Api\TypeHub\Tag;

/**
 * TestPublisher
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TestPublisher implements PublisherInterface
{
    public function __construct(private PublisherInterface $publisher)
    {
    }

    public function get(?string $filterName = null, bool $standalone = false): string
    {
        return $this->publisher->get($filterName, $standalone);
    }

    public function publish(string $name, string $clientId, string $clientSecret, ?string $filterName = null, bool $standalone = false): void
    {
    }

    public function changelog(string $name, string $clientId, string $clientSecret): Changelog
    {
        return new Changelog(['foo' => 'bar'], ['foo' => 'bar'], '0.1.0', 'Initial version');
    }

    public function tag(string $name, string $clientId, string $clientSecret): Tag
    {
        return new Tag('1', '0.1.0');
    }
}
