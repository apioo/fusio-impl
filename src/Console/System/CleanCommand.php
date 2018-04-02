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

namespace Fusio\Impl\Console\System;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * CleanCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CleanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('system:clean')
            ->setDescription('Removes all demo files from the Fusio directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = realpath(PSX_PATH_SRC . '/../');

        if ($baseDir === false) {
            throw new \RuntimeException('Could not determine base dir');
        }

        $files = [
            'build',
            'doc',
            'resources/routes/todo',
            'resources/schema/todo',
            'src/Todo',
            'tests/Api/Todo',
            '.travis.yml',
            'CHANGELOG.md',
            'README.md',
        ];

        $output->writeln('This command removes the following files and directories from the Fusio directory:');
        $output->writeln('');

        $deleteFiles = [];
        foreach ($files as $file) {
            $path = realpath($baseDir . '/' . $file);
            if ($path !== false) {
                if (is_dir($path)) {
                    $output->writeln('- ' . $path);
                    $deleteFiles[] = $path;
                } elseif (is_file($path)) {
                    $output->writeln('- ' . $path);
                    $deleteFiles[] = $path;
                }
            }
        }

        $output->writeln('');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to delete the files and directories? (y/n): ', false);

        if (!$helper->ask($input, $output, $question)) {
            // we dont want to delete any files
            return;
        }

        $this->cleanFiles($deleteFiles);
        $this->commentFiles($baseDir);
    }

    /**
     * @param array $files
     */
    private function cleanFiles(array $files)
    {
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->rmdir($file);
            } elseif (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Removes all files inside a directory recursively
     * 
     * @param string $dir
     */
    private function rmdir($dir)
    {
        // remove all files
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->rmdir($path);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }

        rmdir($path);
    }

    /**
     * @param string $baseDir
     */
    private function commentFiles($baseDir)
    {
        $files = [
            'resources/routes.yaml',
            'resources/schemas.yaml',
        ];

        foreach ($files as $file) {
            $path = $baseDir . '/' . $file;
            if (is_file($path)) {
                $this->commentFile($path, 'todo/');
            }
        }
    }

    /**
     * @param string $file
     * @param string $needle
     */
    private function commentFile($file, $needle)
    {
        if (!is_file($file)) {
            return;
        }

        $lines  = file($file);
        $return = '';

        foreach ($lines as $line) {
            if (strpos($line, $needle) !== false && $line[0] !== '#') {
                $return.= '#' . $line;
            } else {
                $return.= $line;
            }
        }

        file_put_contents($file, $return);
    }
}
