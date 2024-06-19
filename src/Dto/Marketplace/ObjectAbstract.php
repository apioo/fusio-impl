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
abstract class ObjectAbstract implements \JsonSerializable
{
    private string $name;
    private string $version;
    private ?string $icon = null;
    private ?string $summary = null;
    private ?string $description = null;
    private ?string $author = null;
    private ?bool $verified = null;
    private ?int $costs = null;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): void
    {
        $this->summary = $summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): void
    {
        $this->verified = $verified;
    }

    public function getCosts(): ?int
    {
        return $this->costs;
    }

    public function setCosts(?int $costs): void
    {
        $this->costs = $costs;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'icon' => $this->icon,
            'summary' => $this->summary,
            'description' => $this->description,
            'author' => $this->author,
            'verified' => $this->verified,
            'costs' => $this->costs,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return static
     */
    public static function fromObject(object $data): static
    {
        $name = $data->name ?? null;
        if (empty($name)) {
            throw new \InvalidArgumentException('No name provided');
        }

        $version = $data->version ?? null;
        if (empty($version) || !is_string($version)) {
            $version = '0.0.0';
        }

        $object = new static($name, $version);

        if (isset($data->icon) && is_string($data->icon)) {
            $object->setIcon($data->icon);
        }

        if (isset($data->summary) && is_string($data->summary)) {
            $object->setSummary($data->summary);
        }

        if (isset($data->description) && is_string($data->description)) {
            $object->setDescription($data->description);
        }

        if (isset($data->author) && is_string($data->author)) {
            $object->setAuthor($data->author);
        }

        if (isset($data->verified) && is_bool($data->verified)) {
            $object->setVerified($data->verified);
        }

        if (isset($data->costs) && is_int($data->costs)) {
            $object->setCosts($data->costs);
        }

        return $object;
    }
}
