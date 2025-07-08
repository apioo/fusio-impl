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

namespace Fusio\Impl\Service\Marketplace;

/**
 * Factory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Factory
{
    private Action\Factory $actionFactory;
    private App\Factory $appFactory;

    public function __construct(Action\Factory $actionFactory, App\Factory $appFactory)
    {
        $this->actionFactory = $actionFactory;
        $this->appFactory = $appFactory;
    }

    public function factory(Type $type): FactoryInterface
    {
        return match ($type) {
            Type::ACTION => $this->actionFactory,
            Type::APP => $this->appFactory,
        };
    }
}
