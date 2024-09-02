<?php
namespace WeltPixel\GoogleTagManager\lib\Google;

/**
 * Extension to the regular Google_Model that automatically
 * exposes the items array for iteration, so you can just
 * iterate over the object rather than a reference inside.
 */
class Collection extends \WeltPixel\GoogleTagManager\lib\Google\Client implements \Iterator, \Countable
{
    protected $collection_key = 'items';

    public function rewind(): void
    {
        if (isset($this->modelData[$this->collection_key])
            && is_array($this->modelData[$this->collection_key])) {
            reset($this->modelData[$this->collection_key]);
        }
    }

    public function current(): mixed
    {
        $this->coerceType($this->key());
        if (is_array($this->modelData[$this->collection_key])) {
            return current($this->modelData[$this->collection_key]);
        }
        return null; // AÃ±adir un valor de retorno por defecto
    }

    public function key(): mixed
    {
        if (isset($this->modelData[$this->collection_key])
            && is_array($this->modelData[$this->collection_key])) {
            return key($this->modelData[$this->collection_key]);
        }
        return null; // AÃ±adir un valor de retorno por defecto
    }

    public function next(): void
    {
        next($this->modelData[$this->collection_key]);
    }

    public function valid(): bool
    {
        $key = $this->key();
        return $key !== null && $key !== false;
    }

    public function count(): int
    {
        if (!isset($this->modelData[$this->collection_key])) {
            return 0;
        }
        return count($this->modelData[$this->collection_key]);
    }

    public function offsetExists($offset): bool
    {
        if (!is_numeric($offset)) {
            return parent::offsetExists($offset);
        }
        return isset($this->modelData[$this->collection_key][$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if (!is_numeric($offset)) {
            return parent::offsetGet($offset);
        }
        $this->coerceType($offset);
        return $this->modelData[$this->collection_key][$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!is_numeric($offset)) {
            parent::offsetSet($offset, $value);
            return;
        }
        $this->modelData[$this->collection_key][$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if (!is_numeric($offset)) {
            parent::offsetUnset($offset);
            return;
        }
        unset($this->modelData[$this->collection_key][$offset]);
    }

    private function coerceType($offset): void
    {
        $typeKey = $this->keyType($this->collection_key);
        if (isset($this->$typeKey) && !is_object($this->modelData[$this->collection_key][$offset])) {
            $type = $this->$typeKey;
            $this->modelData[$this->collection_key][$offset] = new $type($this->modelData[$this->collection_key][$offset]);
        }
    }
}

