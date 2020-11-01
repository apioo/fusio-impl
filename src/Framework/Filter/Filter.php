<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Framework\Filter;

use Fusio\Impl\Backend\Filter\Route\Path;
use PSX\Api\Listing\FilterInterface;

/**
 * Filter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Filter implements FilterInterface
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function match(string $path): bool
    {
        if ($this->getId() === 'default') {
            $parts = explode('/', $path);
            $name  = $parts[1] ?? null;

            return !in_array($name, self::getReserved());
        } else {
            return substr($path, 1, strlen($this->getId()) + 1) == '/' . $this->getId();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public static function getReserved(): array
    {
        return [
            'backend',
            'consumer',
            'system',
            'authorization',
        ];
    }
}
