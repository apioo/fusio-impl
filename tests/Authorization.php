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

namespace Fusio\Impl\Tests;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Service;

/**
 * Authorization
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Authorization
{
    public const BACKEND_ADMINISTRATOR = 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf';
    public const DEVELOPER_CONSUMER = 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2';
    public const FOOAPP_CONSUMER = 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873';
    public const FOOAPP_DEVELOPER = 'Bearer e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f';
    //public const DEVELOPER_CONSUMER = 'Bearer 1b8fca875fc81c78538d541b3ed0557a34e33feaf71c2ecdc2b9ebd40aade51b';
    public const BACKEND_DEVELOPER = 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a';
    public const FOOAPP_EXPIRED = 'Bearer b41344388feed85bc362e518387fdc9c81b896bfe5e794131e1469770571d873';
}
