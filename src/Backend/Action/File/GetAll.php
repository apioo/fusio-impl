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

namespace Fusio\Impl\Backend\Action\File;

use DateTimeInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GetAll extends FileAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $connection = $this->getConnection($request);
        $startIndex = (int) $request->get('startIndex');
        $count = (int) $request->get('count');
        $limit = 1024;

        $startIndex = $startIndex < 0 ? 0 : $startIndex;
        $count = $count >= 1 && $count <= $limit ? $count : 16;

        $objects = $this->getObjects($connection);

        $totalResults = count($objects);

        usort($objects, static function(StorageAttributes $a, StorageAttributes $b) {
            return strcasecmp($a->path(), $b->path());
        });

        $objects = array_slice($objects, $startIndex, $count);

        $result = [];
        foreach ($objects as $object) {
            if ($object instanceof FileAttributes) {
                $lastModified = $this->getDateTimeFromTimeStamp($connection->lastModified($object->path()));

                $result[] = [
                    'id' => $this->getObjectId($object),
                    'name' => $object->path(),
                    'contentType' => $connection->mimeType($object->path()),
                    'checksum' => $connection->checksum($object->path()),
                    'lastModified' => $lastModified->format(DateTimeInterface::ATOM),
                ];
            }
        }

        return [
            'totalResults' => $totalResults,
            'itemsPerPage' => $count,
            'startIndex' => $startIndex,
            'entry' => $result,
        ];
    }
}
