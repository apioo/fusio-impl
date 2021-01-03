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

namespace Fusio\Impl\Adapter;

use Fusio\Impl\Provider\ProviderWriter;
use stdClass;

/**
 * The installer inserts only the action and connection classes through the
 * database connection. All other entries are inserted through the API endpoint
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Installer
{
    /**
     * @var ProviderWriter
     */
    private $providerWriter;

    public function __construct(ProviderWriter $providerWriter)
    {
        $this->providerWriter = $providerWriter;
    }

    /**
     * @param array $instructions
     */
    public function install(array $instructions)
    {
        $data = new stdClass();

        foreach ($instructions as $instruction) {
            $key   = $instruction->getKey();
            $value = $instruction->getPayload();

            if (!isset($data->$key)) {
                $data->$key = [];
            }

            array_push($data->$key, $value);
        }

        $this->importProvider($data);
    }

    /**
     * @param \stdClass $data
     */
    private function importProvider(stdClass $data)
    {
        $providerTypes  = $this->providerWriter->getAvailableTypes();
        $providerConfig = [];
        $newClasses     = [];

        foreach ($providerTypes as $providerType) {
            $name    = $providerType . 'Class';
            $classes = isset($data->{$name}) ? $data->{$name} : null;
            if (!empty($classes) && is_array($classes)) {
                $classes    = array_filter($classes, 'class_exists');
                $newClasses = array_merge($newClasses, $classes);

                $providerConfig[$providerType] = $classes;
            }
        }

        $this->providerWriter->write($providerConfig);
    }
}
