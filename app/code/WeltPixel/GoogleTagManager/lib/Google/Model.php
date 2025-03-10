<?php
namespace WeltPixel\GoogleTagManager\lib\Google;

use \WeltPixel\GoogleTagManager\lib\Google\Exception as Google_Exception;

class Model implements \ArrayAccess
{
    public const NULL_VALUE = "{}gapi-php-null";
    protected $internal_gapi_mappings = [];
    protected $modelData = [];
    protected $processed = [];

    public function __construct()
    {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $array = func_get_arg(0);
            $this->mapTypes($array);
        }
        $this->gapiInit();
    }

    public function __get($key)
    {
        $keyTypeName = $this->keyType($key);
        $keyDataType = $this->dataType($key);
        if (isset($this->$keyTypeName) && !isset($this->processed[$key])) {
            if (isset($this->modelData[$key])) {
                $val = $this->modelData[$key];
            } else if (isset($this->$keyDataType) &&
                ($this->$keyDataType == 'array' || $this->$keyDataType == 'map')) {
                $val = [];
            } else {
                $val = null;
            }

            if ($this->isAssociativeArray($val)) {
                if (isset($this->$keyDataType) && 'map' == $this->$keyDataType) {
                    foreach ($val as $arrayKey => $arrayItem) {
                        $this->modelData[$key][$arrayKey] =
                            $this->createObjectFromName($keyTypeName, $arrayItem);
                    }
                } else {
                    $this->modelData[$key] = $this->createObjectFromName($keyTypeName, $val);
                }
            } else if (is_array($val)) {
                $arrayObject = [];
                foreach ($val as $arrayIndex => $arrayItem) {
                    $arrayObject[$arrayIndex] =
                        $this->createObjectFromName($keyTypeName, $arrayItem);
                }
                $this->modelData[$key] = $arrayObject;
            }
            $this->processed[$key] = true;
        }

        return $this->modelData[$key] ?? null;
    }

    protected function mapTypes($array)
    {
        foreach ($array as $key => $val) {
            if (!property_exists($this, $this->keyType($key)) &&
                property_exists($this, $key)) {
                $this->$key = $val;
                unset($array[$key]);
            } elseif (property_exists($this, $camelKey = Google_Utils::camelCase($key))) {
                $this->$camelKey = $val;
            }
        }
        $this->modelData = $array;
    }

    protected function gapiInit()
    {
        return;
    }

    public function toSimpleObject()
    {
        $object = new \stdClass();

        foreach ($this->modelData as $key => $val) {
            $result = $this->getSimpleValue($val);
            if ($result !== null) {
                $object->$key = $this->nullPlaceholderCheck($result);
            }
        }

        $reflect = new \ReflectionObject($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $member) {
            $name = $member->getName();
            $result = $this->getSimpleValue($this->$name);
            if ($result !== null) {
                $name = $this->getMappedName($name);
                $object->$name = $this->nullPlaceholderCheck($result);
            }
        }

        return $object;
    }

    private function getSimpleValue($value)
    {
        if ($value instanceof self) {
            return $value->toSimpleObject();
        } else if (is_array($value)) {
            $return = [];
            foreach ($value as $key => $a_value) {
                $a_value = $this->getSimpleValue($a_value);
                if ($a_value !== null) {
                    $key = $this->getMappedName($key);
                    $return[$key] = $this->nullPlaceholderCheck($a_value);
                }
            }
            return $return;
        }
        return $value;
    }

    private function nullPlaceholderCheck($value)
    {
        if ($value === self::NULL_VALUE) {
            return null;
        }
        return $value;
    }

    private function getMappedName($key)
    {
        if (isset($this->internal_gapi_mappings) &&
            isset($this->internal_gapi_mappings[$key])) {
            $key = $this->internal_gapi_mappings[$key];
        }
        return $key;
    }

    protected function isAssociativeArray($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $keys = array_keys($array);
        foreach ($keys as $key) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }

    private function createObjectFromName($name, $item)
    {
        $type = $this->$name;
        return new $type($item);
    }

    public function assertIsArray($obj, $method)
    {
        if ($obj && !is_array($obj)) {
            throw new Google_Exception(
                "Incorrect parameter type passed to $method(). Expected an array."
            );
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->$offset) || isset($this->modelData[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->$offset ??
            $this->__get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
        } else {
            $this->modelData[$offset] = $value;
            $this->processed[$offset] = true;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->modelData[$offset]);
    }

    protected function keyType($key)
    {
        return $key . "Type";
    }

    protected function dataType($key)
    {
        return $key . "DataType";
    }

    public function __isset($key)
    {
        return isset($this->modelData[$key]);
    }

    public function __unset($key)
    {
        unset($this->modelData[$key]);
    }
}

