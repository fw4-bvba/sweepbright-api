<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Response;

use SweepBright\Exception\ClientValidationException;
use DateTime;
use JsonSerializable;

class ResponseObject implements JsonSerializable
{
    protected $_data = [];
    private $_propertyIndex = [];

    public function __construct($data)
    {
        foreach ($data as $property => &$value) {
            $this->_propertyIndex[$property] = $property;
            $this->$property = $this->parseValue($value, $property);
        }
    }

    protected function parseValue($value, ?string $property = null)
    {
        if (is_object($value)) {
            return new self($value);
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as &$subvalue) {
                $result[] = $this->parseValue($subvalue);
            }
            return $result;
        } elseif (is_string($value) && preg_match('/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:\.\d{1,9})?(?:Z|[+-][01]\d:[0-5]\d)$/', $value)) {
            return new DateTime($value);
        } else {
            return $value;
        }
    }

    public function getData(): array
    {
        return $this->_data;
    }

    public function __get(string $property)
    {
        $property = $this->normalizePropertyName($property);
        return $this->_data[$property] ?? null;
    }

    public function __set(string $property, $value)
    {
        $this->_propertyIndex[strtolower($property)] = $property;
        $this->_data[$property] = $value;
    }

    public function __isset(string $property): bool
    {
        return isset($this->_data[$this->convertToCamelCase($property)]);
    }

    public function __unset(string $property)
    {
        $property = $this->normalizePropertyName($property);
        unset($this->_data[$property]);
    }

    public function __debugInfo()
    {
        return $this->getData();
    }

    protected function normalizePropertyName(string $property): string
    {
        $property = $this->convertToCamelCase($property);
        if (empty($this->_propertyIndex[$property])) {
            throw new ClientValidationException($property . ' is not a valid property of ' . static::class);
        }
        return $property;
    }

    protected function convertToCamelCase(string $property): string
    {
        if (isset($this->_propertyIndex[$property])) {
            return $this->_propertyIndex[$property];
        } else {
            $normalized = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $property)), '_');
            if (!empty($this->_propertyIndex[$normalized])) {
                $this->_propertyIndex[$property] = $normalized;
            }
            return $normalized;
        }
    }

    /* JsonSerializable implementation */

    public function jsonSerialize()
    {
        return $this->getData();
    }
}
