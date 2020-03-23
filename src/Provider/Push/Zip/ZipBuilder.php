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

namespace Fusio\Impl\Provider\Push\Zip;

/**
 * ZipBuilder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ZipBuilder
{
    /**
     * @var array
     */
    private $includeDirs  = [
        'bin',
        'apps',
        'public',
        'resources',
        'src',
        'tests',
    ];

    /**
     * @var array
     */
    private $includeFiles = [
        '.fusio.yml',
        'composer.json',
        'composer.lock',
        'configuration.php',
        'container.php',
        'provider.php',
    ];

    /**
     * @var array
     */
    private $excludeDirs  = [
        '\.(.*)', // i.e. .git
        'node_modules',
        'vendor',
    ];

    /**
     * @var array
     */
    private $excludeFiles = [
        '\.(.*)', // i.e. .travis.yml
    ];

    /**
     * Creates a zip archive for the provided file using all files located at 
     * the base path
     * 
     * @param string $file
     * @param string $basePath
     * @return \Generator
     */
    public function buildZip($file, $basePath)
    {
        yield 'Generating zip file ' . $file;

        $zip = new \ZipArchive();
        $res = $zip->open($file, \ZipArchive::CREATE);

        if ($res === true) {
            $this->buildZipArchive($basePath, $zip, $basePath, 0);
            $zip->close();

            yield 'Generation completed file size ' . (round(filesize($file) / 1024, 2)) . 'kb';
        } else {
            throw new \RuntimeException('Could not create zip file ' . $file);
        }
    }

    /**
     * @param string $path
     * @param \ZipArchive $zip
     * @param string $basePath
     * @param integer $depth
     */
    private function buildZipArchive($path, \ZipArchive $zip, $basePath, $depth)
    {
        $dir = new \RecursiveDirectoryIterator($path);
        foreach ($dir as $path => $file) {
            /** @var \SplFileInfo $file */
            if ($file->getFilename() == '.' || $file->getFilename() == '..') {
                continue;
            }

            if ($file->isDir()) {
                if ($depth === 0) {
                    // at the first level we include only specific dirs
                    if ($this->inArray($file->getFilename(), $this->includeDirs)) {
                        $this->buildZipArchive($path, $zip, $basePath, $depth + 1);
                    }
                } else {
                    // at all levels below we have a blacklist
                    if (!$this->inArray($file->getFilename(), $this->excludeDirs)) {
                        $this->buildZipArchive($path, $zip, $basePath, $depth + 1);
                    }
                }
            } elseif ($file->isFile()) {
                $relativePath = substr($path, strlen($basePath) + 1);

                if ($depth === 0) {
                    // at the first level we include only specific files
                    if ($this->inArray($file->getFilename(), $this->includeFiles)) {
                        $zip->addFile($path, $relativePath);
                    }
                } else {
                    // at all levels below we have a blacklist
                    if (!$this->inArray($file->getFilename(), $this->excludeFiles)) {
                        $zip->addFile($path, $relativePath);
                    }
                }
            }
        }
    }

    /**
     * @param string $file
     * @param array $list
     * @return boolean
     */
    private function inArray($file, array $list)
    {
        foreach ($list as $regexp) {
            if (preg_match('/^' . $regexp . '$/', $file)) {
                return true;
            }
        }

        return false;
    }
}
