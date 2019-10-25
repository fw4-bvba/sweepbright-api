<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Request;

use SweepBright\Exception\ClientValidationException;

abstract class RequestObject implements \JsonSerializable
{
    protected $_data = [];
    
    private static $_propertyDefinitions = [];
    private static $_propertyIndex = [];

    public function __construct(array $properties = [])
    {
        self::initStatic();
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    public function __get(string $property)
    {
        $property = self::normalizePropertyName($property);
        return $this->_data[$property] ?? null;
    }

    public function __set(string $property, $value)
    {
        $property = self::normalizePropertyName($property);
        if (self::propertyDefinitionIsArray($property)) {
            if (!is_array($value) && !(is_object($value) && ($value instanceof \Traversable))) {
                throw ClientValidationException::forExpectedType(static::class, $property, 'array', $value);
            }
            $this->_data[$property] = [];
            foreach ($value as $subvalue) {
                $this->_data[$property][] = self::validateParameter($property, $subvalue);
            }
        } else {
            $this->_data[$property] = self::validateParameter($property, $value);
        }
    }

    public function __isset(string $property): bool
    {
        $property = self::normalizePropertyName($property);
        return isset($this->_data[$property]);
    }

    public function __unset(string $property)
    {
        $property = self::normalizePropertyName($property);
        unset($this->_data[$property]);
    }

    public function __debugInfo(): array
    {
        return $this->getData();
    }

    public function getData(): array
    {
        $data = [];
        foreach (self::getCachedPropertyDefinitions() as $name => $type) {
            if (isset($this->_data[$name])) {
                if (self::propertyDefinitionIsArray($name)) {
                    $data[$name] = array_values($this->_data[$name]);
                } else {
                    $data[$name] = $this->_data[$name];
                }
            }
        }
        return $data;
    }

    /* Static methods */

    protected static function initStatic()
    {
        if (empty(self::$_propertyDefinitions[static::class])) {
            self::$_propertyDefinitions[static::class] = static::getPropertyDefinitions();
            self::$_propertyIndex[static::class] = [];
            foreach (self::$_propertyDefinitions[static::class] as $name => $type) {
                self::$_propertyIndex[static::class][$name]  = $name;
            }
        }
    }

    protected static function normalizePropertyName(string $property): string
    {
        $property = self::convertToCamelCase($property);
        if (empty(self::$_propertyDefinitions[static::class][$property])) {
            throw new ClientValidationException($property . ' is not a valid parameter for ' . static::class);
        }
        return $property;
    }

    protected static function convertToCamelCase(string $property): string
    {
        if (isset(self::$_propertyIndex[static::class][$property])) {
            return self::$_propertyIndex[static::class][$property];
        } else {
            $normalized = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $property)), '_');
            if (!empty(self::$_propertyDefinitions[static::class][$normalized])) {
                self::$_propertyIndex[static::class][$property] = $normalized;
            }
            return $normalized;
        }
    }

    protected static function getCachedPropertyDefinitions(): array
    {
        return self::$_propertyDefinitions[static::class] ?? [];
    }

    protected static function getPropertyDefinitions(): array
    {
        return static::PROPERTIES;
    }

    protected static function getPropertyDefinition(string $property): string
    {
        if (self::propertyDefinitionIsArray($property)) {
            return self::$_propertyDefinitions[static::class][$property][0];
        } else {
            return self::$_propertyDefinitions[static::class][$property];
        }
    }

    protected static function propertyDefinitionIsArray(string $property): bool
    {
        return is_array(self::$_propertyDefinitions[static::class][$property]);
    }

    protected static function validateParameter(string $property, $value)
    {
        if (is_null($value)) {
            return null;
        }
        $definition = self::getPropertyDefinition($property);
        switch ($definition) {
            case 'integer':
                $value = intval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'numeric':
                if (!is_int($value) && !is_float($value)) {
                    if (is_string($value)) {
                        if (strlen(trim($value)) === 0) {
                            $value = null;
                        } elseif (strval(intval($value)) === $value) {
                            $value = intval($value);
                        } else {
                            $value = floatval($value);
                        }
                    } else {
                        $value = intval($value);
                    }
                }
                break;
            case 'string':
                $value = strval($value);
                break;
            case 'boolean':
                $value = !!$value;
                break;
            default:
                if (is_array($value)) {
                    $value = new $definition($value);
                } elseif (!$value instanceof $definition) {
                    throw ClientValidationException::forExpectedType(static::class, $property, $definition, $value);
                }
        }
        return $value;
    }

    /* JsonSerializable implementation */

    public function jsonSerialize()
    {
        return $this->getData();
    }
}
