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

namespace Fusio\Impl\Service\Marketplace\Repository;

use Fusio\Impl\Service\Marketplace\App;
use Fusio\Impl\Service\Marketplace\RepositoryInterface;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\Options;
use Symfony\Component\Yaml\Yaml;

/**
 * Remote
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Remote implements RepositoryInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $marketplaceUrl;

    /**
     * @var boolean
     */
    private $sslVerify;

    /**
     * @var array
     */
    private $apps;

    /**
     * @param ClientInterface $httpClient
     * @param string $marketplaceUrl
     */
    public function __construct(ClientInterface $httpClient, string $marketplaceUrl)
    {
        $this->httpClient = $httpClient;
        $this->marketplaceUrl = $marketplaceUrl;
        $this->sslVerify = true;
    }

    /**
     * @param bool $sslVerify
     */
    public function setSslVerify(bool $sslVerify)
    {
        $this->sslVerify = $sslVerify;
    }

    /**
     * @inheritDoc
     */
    public function fetchAll(): array
    {
        if (!$this->apps) {
            $this->apps = $this->request();
        }

        return $this->apps;
    }

    /**
     * @inheritDoc
     */
    public function fetchByName(string $name): ?App
    {
        $apps = $this->fetchAll();

        return $apps[$name] ?? null;
    }

    /**
     * Downloads the provided app to the app file
     * 
     * @param App $app
     * @param string $appFile
     */
    public function downloadZip(App $app, string $appFile): void
    {
        // increase timeout to handle download
        set_time_limit(300);

        $options = new Options();
        $options->setVerify($this->sslVerify);
        $options->setAllowRedirects(true);

        $response = $this->httpClient->request(new GetRequest($app->getDownloadUrl()), $options);

        file_put_contents($appFile, $response->getBody()->getContents());
    }

    private function request(): array
    {
        $options = new Options();
        $options->setVerify($this->sslVerify);

        $response = $this->httpClient->request(new GetRequest($this->marketplaceUrl), $options);

        if ($response->getStatusCode() > 300) {
            throw new \RuntimeException('Could not fetch repository, received ' . $response->getStatusCode());
        }

        $body = (string) $response->getBody();
        $data = Yaml::parse($body);

        if (is_iterable($data)) {
            return $this->parse($data);
        } else {
            throw new \RuntimeException('Could not parse repository response');
        }
    }

    private function parse(iterable $data): array
    {
        $result = [];
        foreach ($data as $name => $meta) {
            $result[$name] = App::fromArray($name, $meta);
        }

        return $result;
    }
}
