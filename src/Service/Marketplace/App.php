<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @link    http://fusio-project.org
 */
class App
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $screenshot;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $downloadUrl;

    /**
     * @var string
     */
    private $sha1Hash;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getScreenshot(): string
    {
        return $this->screenshot;
    }

    /**
     * @param string $screenshot
     */
    public function setScreenshot(string $screenshot): void
    {
        $this->screenshot = $screenshot;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $downloadUrl
     */
    public function setDownloadUrl(string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * @return string
     */
    public function getSha1Hash(): string
    {
        return $this->sha1Hash;
    }

    /**
     * @param string $sha1Hash
     */
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
