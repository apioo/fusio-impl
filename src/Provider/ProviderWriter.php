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

namespace Fusio\Impl\Provider;

use Fusio\Engine;

/**
 * ProviderWriter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderWriter
{
    /**
     * @var \Fusio\Impl\Provider\ProviderConfig 
     */
    protected $config;

    /**
     * @var string
     */
    protected $file;

    /**
     * @param \Fusio\Impl\Provider\ProviderConfig $config
     * @param string $file
     */
    public function __construct(ProviderConfig $config, $file)
    {
        $this->config = $config;
        $this->file   = $file;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return array_keys($this->config->getArrayCopy());
    }

    /**
     * @param array $newConfig
     * @return integer|boolean
     */
    public function write(array $newConfig)
    {
        if (empty($newConfig) || empty($this->file)) {
            return false;
        }

        if (!is_file($this->file)) {
            return false;
        }

        if (!is_writable($this->file)) {
            return false;
        }

        $existingConfig = $this->config->getArrayCopy();
        $hasChanged     = false;
        $resultConfig   = [];

        foreach ($existingConfig as $type => $classes) {
            $result = array_values($classes);
            if (isset($newConfig[$type]) && is_array($newConfig[$type])) {
                $newClasses = $this->parseClasses($newConfig[$type]);

                if (!empty($newClasses)) {
                    $result = array_merge($result, $newClasses);
                    $result = array_unique(array_values($result));
                }
            }

            if (count($classes) !== count($result) || count(array_diff($result, $classes)) > 0) {
                $hasChanged = true;
            }

            $resultConfig[$type] = $result;
        }

        if ($hasChanged) {
            return file_put_contents($this->file, $this->generateCode($resultConfig));
        }

        return false;
    }

    private function parseClasses(array $classes)
    {
        $result = [];
        foreach ($classes as $class) {
            try {
                $result[] = (new \ReflectionClass($class))->getName();
            } catch (\ReflectionException $e) {
                // class does not exist
            }
        }

        return $result;
    }

    private function generateCode(array $config)
    {
        $code = '<?php' . "\n";
        $code.= '' . "\n";
        $code.= '/*' . "\n";
        $code.= $this->getHelpDoc() . "\n";
        $code.= '*/' . "\n";
        $code.= '' . "\n";
        $code.= 'return [' . "\n";

        foreach ($config as $type => $classes) {
            $code.= '    \'' . $type . '\' => [' . "\n";
            foreach ($classes as $class) {
                $code.= '        \\' . ltrim($class, '\\') . '::class,' . "\n";
            }
            $code.= '    ],' . "\n";
        }

        $code.= '];' . "\n";
        $code.= '' . "\n";

        return $code;
    }

    private function getHelpDoc()
    {
        $actionInterface     = Engine\ActionInterface::class;
        $connectionInterface = Engine\ConnectionInterface::class;
        $paymentInterface    = Engine\Payment\ProviderInterface::class;
        $userInterface       = Engine\User\ProviderInterface::class;

        return <<<TEXT
This file contains classes which extend the functionality of Fusio. If you
register a new adapter and this adapter provides such a class, Fusio will
automatically add the class to this file. You can also manually add a new
class. The following list contains an explanation of each extension point:

- action
  Contains all action classes which are available at the backend. If a class is
  registered here the user can select this action. The class must implement the
  interface: {$actionInterface}
- connection
  Contains all connection classes which are available at the backend. If a class
  is registered here the user can select this connection. The class must
  implement the interface: {$connectionInterface}
- payment
  Contains all available payment provider. Through a payment provider it is
  possible to charge for points which can be required for specific routes. The
  class must implement the interface: {$paymentInterface}
- user
  Contains all available user provider. Through a user provider a user can
  authenticate with a remote provider i.e. Google. The class must implement the
  interface: {$userInterface}
TEXT;
    }
}
