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

namespace Fusio\Impl\Command\System;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ClearCacheCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ClearCacheCommand extends Command
{
    private CacheItemPoolInterface $cache;
    private CacheInterface $engineCache;

    public function __construct(CacheItemPoolInterface $cache, CacheInterface $engineCache)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->engineCache = $engineCache;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:clear_cache')
            ->setDescription('Clears the complete cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cache->clear();
        $this->engineCache->clear();

        $output->writeln('Cache cleared');

        return self::SUCCESS;
    }
}
