<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Marketplace;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class App
{
    private string $name;
    private ?string $version = null;
    private ?string $description = null;
    private ?string $screenshot = null;
    private ?string $website = null;
    private ?string $downloadUrl = null;
    private ?string $sha1Hash = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getScreenshot(): ?string
    {
        return $this->screenshot;
    }

    public function setScreenshot(string $screenshot): void
    {
        $this->screenshot = $screenshot;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl(string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    public function getSha1Hash(): ?string
    {
        return $this->sha1Hash;
    }

    public function setSha1Hash(string $sha1Hash): void
    {
        $this->sha1Hash = $sha1Hash;
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'description' => $this->description,
            'screenshot' => $this->screenshot,
            'website' => $this->website,
            'downloadUrl' => $this->downloadUrl,
            'sha1Hash' => $this->sha1Hash,
        ];
    }

    public static function fromArray(string $name, array $data)
    {
        $app = new static($name);

        if (isset($data['version'])) {
            $app->setVersion($data['version']);
        }

        if (isset($data['description'])) {
            $app->setDescription($data['description']);
        }

        if (isset($data['screenshot'])) {
            $app->setScreenshot($data['screenshot']);
        }

        if (isset($data['website'])) {
            $app->setWebsite($data['website']);
        }

        if (isset($data['downloadUrl'])) {
            $app->setDownloadUrl($data['downloadUrl']);
        }

        if (isset($data['sha1Hash'])) {
            $app->setSha1Hash($data['sha1Hash']);
        }

        return $app;
    }
}
