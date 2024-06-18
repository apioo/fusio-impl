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

namespace Fusio\Impl\Service\Marketplace;

use Fusio\Impl\Dto\Marketplace\Collection;
use Fusio\Impl\Dto\Marketplace\ObjectAbstract;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\Options;
use PSX\Uri\Uri;

/**
 * Remote
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class RemoteAbstract implements RepositoryInterface
{
    private ClientInterface $httpClient;
    private string $marketplaceUrl;
    private bool $sslVerify;

    public function __construct(ClientInterface $httpClient, FrameworkConfig $frameworkConfig)
    {
        $this->httpClient = $httpClient;
        $this->marketplaceUrl = $frameworkConfig->getMarketplaceUrl();
        $this->sslVerify = true;
    }

    public function setSslVerify(bool $sslVerify): void
    {
        $this->sslVerify = $sslVerify;
    }

    public function fetchAll(int $startIndex = 0, ?string $query = null): Collection
    {
        $options = new Options();
        $options->setVerify($this->sslVerify);

        $uri = Uri::parse($this->marketplaceUrl)->withPath($this->getPath())->withParameters(['startIndex' => $startIndex, 'query' => $query]);
        $response = $this->httpClient->request(new GetRequest($uri), $options);

        if ($response->getStatusCode() > 300) {
            throw new \RuntimeException('Could not fetch repository, received ' . $response->getStatusCode());
        }

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $totalResults = $data->totalResults ?? 0;
        $startIndex = $data->startIndex ?? 0;
        $itemsPerPage = $data->itemsPerPage ?? 0;
        $entries = $data->entry ?? [];

        $result = new Collection($totalResults, $startIndex, $itemsPerPage);
        foreach ($entries as $entry) {
            $result->addObject($this->parse($entry));
        }

        return $result;
    }

    public function fetchByName(string $name): ?ObjectAbstract
    {
        $options = new Options();
        $options->setVerify($this->sslVerify);

        $uri = Uri::parse($this->marketplaceUrl)->withPath($this->getPath() . '/' . $name);
        $response = $this->httpClient->request(new GetRequest($uri), $options);

        if ($response->getStatusCode() === 404) {
            return null;
        }

        if ($response->getStatusCode() > 300) {
            throw new \RuntimeException('Could not fetch repository, received ' . $response->getStatusCode());
        }

        $body = (string) $response->getBody();

        $data = \json_decode($body);
        if (!$data instanceof \stdClass) {
            throw new \RuntimeException('The marketplace server returned an invalid payload');
        }

        return $this->parse($data);
    }

    abstract protected function getPath(): string;

    abstract protected function parse(\stdClass $data): ObjectAbstract;
}
