<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Template\Extension;

use PSX\Data\Writer;
use PSX\Record\Record;
use PSX\Record\RecordInterface;

/**
 * Text
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Text extends Base
{
    public function __construct()
    {
        $this->setDateFormat(\DateTime::ISO8601);
        $this->setNumberFormat(2, '.', '');
        $this->setEscaper('json', function($value){
            return json_encode($value);
        });
    }

    public function getFilters()
    {
        return array_merge(parent::getFilters(), [
            new \Twig_SimpleFilter('string', __NAMESPACE__ . '\\fusio_string_filter'),
            new \Twig_SimpleFilter('number', __NAMESPACE__ . '\\fusio_number_filter'),
            new \Twig_SimpleFilter('object', __NAMESPACE__ . '\\fusio_object_filter'),
            new \Twig_SimpleFilter('json', __NAMESPACE__ . '\\fusio_object_filter'), // deprecated
            new \Twig_SimpleFilter('array', __NAMESPACE__ . '\\fusio_array_filter'),
            new \Twig_SimpleFilter('boolean', __NAMESPACE__ . '\\fusio_boolean_filter'),
            new \Twig_SimpleFilter('integer', __NAMESPACE__ . '\\fusio_integer_filter'),
        ]);
    }
}

function fusio_string_filter($value, $default = '') {
    if ($value === '' || $value === null) {
        return json_encode((string) $default);
    } else {
        return json_encode((string) $value);
    }
}

function fusio_boolean_filter($value, $default = false) {
    if ($value === '' || $value === null) {
        return json_encode((bool) $default);
    } else {
        return json_encode($value === 'false' ? false : (bool) $value);
    }
}

function fusio_integer_filter($value, $default = 0, $min = null, $max = null) {
    if ($value === '' || $value === null) {
        return json_encode((int) $default);
    } else {
        $value = (int) $value;
        if ($min !== null && $max !== null) {
            if ($value >= $min && $value <= $max) {
                // ok
            } else {
                $value = (int) $default;
            }
        } elseif ($min !== null && $max === null) {
            if ($value <= $min) {
                // ok
            } else {
                $value = (int) $default;
            }
        }
        return json_encode($value);
    }
}

function fusio_number_filter($value, $default = 0, $min = null, $max = null) {
    if ($value === '' || $value === null) {
        return json_encode((float) $default);
    } else {
        $value = (float) $value;
        if ($min !== null && $max !== null) {
            if ($value >= $min && $value <= $max) {
                // ok
            } else {
                $value = (float) $default;
            }
        } elseif ($min !== null && $max === null) {
            if ($value <= $min) {
                // ok
            } else {
                $value = (float) $default;
            }
        }
        return json_encode((float) $value);
    }
}

function fusio_array_filter($value, array $default = array(), $delimiter = ',') {
    if ($value === '' || $value === null) {
        return json_encode($default);
    } else {
        if (is_array($value)) {
            return json_encode(array_values($value));
        } elseif (is_string($value)) {
            return json_encode(explode($delimiter, $value));
        } else {
            return json_encode($default);
        }
    }
}

function fusio_object_filter($value, $default = null) {
    if ($value === '' || $value === null) {
        return json_encode($default === null ? new \stdClass() : (object) $default);
    }

    if (is_array($value)) {
        $value = Record::fromArray($value);
    } elseif ($value instanceof \stdClass) {
        $value = Record::fromStdClass($value);
    }

    if ($value instanceof RecordInterface) {
        $writer = new Writer\Json();
        return $writer->write($value);
    } else {
        return json_encode($default === null ? new \stdClass() : (object) $default);
    }
}
