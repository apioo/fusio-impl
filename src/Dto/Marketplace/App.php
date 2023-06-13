<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Dto\Marketplace;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class App
{
    private string $name;
    private string $version;
    private ?string $downloadUrl = null;
    private ?string $sha1Hash = null;
    private ?string $description = null;
    private ?string $screenshot = null;
    private ?string $website = null;

    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
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

    public static function fromArray(string $name, array $data): static
    {
        $version = $data['version'] ?? null;
        if (empty($version) || !is_string($version)) {
            $version = '0.0.0';
        }

        $app = new static($name, $version);

        if (isset($data['downloadUrl']) && is_string($data['downloadUrl'])) {
            $app->setDownloadUrl($data['downloadUrl']);
        }

        if (isset($data['sha1Hash']) && is_string($data['sha1Hash'])) {
            $app->setSha1Hash($data['sha1Hash']);
        }

        if (isset($data['description']) && is_string($data['description'])) {
            $app->setDescription($data['description']);
        }

        if (isset($data['screenshot']) && is_string($data['screenshot'])) {
            $app->setScreenshot($data['screenshot']);
        }

        if (isset($data['website']) && is_string($data['website'])) {
            $app->setWebsite($data['website']);
        }

        return $app;
    }
}
