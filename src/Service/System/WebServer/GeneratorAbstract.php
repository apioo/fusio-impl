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

namespace Fusio\Impl\Service\System\WebServer;

/**
 * GeneratorAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
abstract class GeneratorAbstract implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(Configuration $configuration, $file)
    {
        $virtualHosts = $configuration->getVirtualHosts();
        $date = new \DateTime();

        $config = [];
        $config[] = '# Fusio (https://www.fusio-project.org/)';
        $config[] = '# Generated for ' . ucfirst($this->getName()) . ' on ' . $date->format('Y-m-d');
        foreach ($virtualHosts as $virtualHost) {
            $config[] = '# ' . $virtualHost->getServerName();
            $config[] = $this->render([
                'host' => $virtualHost,
            ]);
        }

        return $this->writeConfig($file, implode("\n", $config));
    }

    /**
     * @return string
     */
    protected function getName()
    {
        $class = new \ReflectionObject($this);
        $name  = strtolower($class->getShortName());

        return $name;
    }
    
    /**
     * @param array $context
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function render(array $context)
    {
        $loader = new \Twig_Loader_Filesystem([__DIR__ . '/Generator/Resource']);
        $twig   = new \Twig_Environment($loader, [
            'cache' => PSX_PATH_CACHE,
            'debug' => true,
            'autoescape' => false,
        ]);

        return $twig->render($this->getName() . '.conf.twig', $context);
    }

    /**
     * @param string $file
     * @param string $config
     * @return integer
     */
    private function writeConfig($file, $config)
    {
        if (empty($file)) {
            return false;
        }

        if (!is_file($file)) {
            return false;
        }

        if (!is_writable($file)) {
            return false;
        }

        return file_put_contents($file, $config);
    }
}
