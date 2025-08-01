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

namespace Fusio\Impl\Backend\Action\Connection\Filesystem;

use DateTimeImmutable;
use Exception;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\Connector;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem as Flysystem;
use PSX\Data\Multipart\Body;
use PSX\Data\Multipart\File;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use Ramsey\Uuid\Uuid;

/**
 * FileAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly abstract class FileAbstract implements ActionInterface
{
    public function __construct(private Connector $connector, private FrameworkConfig $frameworkConfig)
    {
    }

    protected function getConnection(RequestInterface $request): Flysystem
    {
        $connectionId = $request->get('connection_id');
        if (empty($connectionId)) {
            throw new StatusCode\BadRequestException('Provided no connection');
        }

        $connection = $this->connector->getConnection($connectionId);
        if (!$connection instanceof Flysystem) {
            throw new StatusCode\BadRequestException('Provided an invalid connection');
        }

        return $connection;
    }

    protected function getObjects(Flysystem $connection): array
    {
        $result = $connection->listContents('.');
        $result = $result->filter(static function ($object) {
            return $object instanceof FileAttributes;
        });

        return iterator_to_array($result);
    }

    protected function getObjectId(FileAttributes $object): string
    {
        return Uuid::uuid3('f3cc3100-3111-4ff2-9554-6511ccdc0490', $object->path())->toString();
    }

    protected function getDateTimeFromTimeStamp(int $timeStamp): LocalDateTime
    {
        try {
            return LocalDateTime::from(new DateTimeImmutable('@' . $timeStamp));
        } catch (Exception) {
            throw new StatusCode\InternalServerErrorException('Provided an invalid timestamp');
        }
    }

    protected function findObjectById(Flysystem $connection, string $id): FileAttributes
    {
        $objects = $this->getObjects($connection);

        foreach ($objects as $object) {
            if ($object instanceof FileAttributes && $this->getObjectId($object) === $id) {
                return $object;
            }
        }

        throw new StatusCode\NotFoundException('Provided in invalid id');
    }

    /**
     * @return iterable<resource>
     */
    protected function getUploadedFiles(mixed $body): iterable
    {
        if (!$body instanceof Body) {
            throw new StatusCode\BadRequestException('Request must be an multipart form upload');
        }

        foreach ($body->getAll() as $part) {
            if (!$part instanceof File) {
                continue;
            }

            if ($part->getError() !== UPLOAD_ERR_OK) {
                throw new StatusCode\BadRequestException('There was an error with the file upload');
            }

            $name = $part->getName();
            if (empty($name)) {
                throw new StatusCode\BadRequestException('Provided no file name');
            }

            if (!preg_match('/^[A-Za-z0-9-_.]{3,64}$/', $name)) {
                throw new StatusCode\BadRequestException('Provided file name contains invalid characters');
            }

            $tmpName = $part->getTmpName();
            if (empty($tmpName) || !is_file($tmpName)) {
                throw new StatusCode\BadRequestException('Could not find uploaded file');
            }

            $handle = fopen($tmpName, 'r');
            if (!is_resource($handle)) {
                throw new StatusCode\BadRequestException('Could not read uploaded file');
            }

            yield $name => $handle;
        }
    }

    protected function assertFilesystemEnabled(): void
    {
        if (!$this->frameworkConfig->isFilesystemEnabled()) {
            throw new StatusCode\ServiceUnavailableException('Filesystem is not enabled, please change the setting "fusio_filesystem" at the configuration.php to "true" in order to activate the filesystem');
        }
    }
}
