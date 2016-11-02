<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\Api\Import;

use Fusio\Impl\Adapter\Transform;
use Fusio\Impl\Authorization\ProtectionTrait;
use PSX\Framework\Controller\ApiAbstract;

/**
 * Format
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Format extends ApiAbstract
{
    use ProtectionTrait;

    public function onPost()
    {
        $format = $this->getUriFragment('format');
        $schema = $this->getAccessor()->get('/schema');

        if ($format == 'raml') {
            $transformer = new Transform\Raml();
        } elseif ($format == 'swagger') {
            $transformer = new Transform\Swagger();
        } else {
            throw new \RuntimeException('Invalid format');
        }

        $this->setBody($transformer->transform($schema));
    }
}
