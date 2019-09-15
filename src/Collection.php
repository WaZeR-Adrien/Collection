<?php declare(strict_types=1);

namespace AdrienM\Collection;

use AdrienM\Logger\Logger;

class Collection
{
    /**
     * Items of the collection
     * @var array
     */
    private $items = [];

    /**
     * All alias to call the good method
     * @var array
     */
    private $alias = [
        "count" => "length", "all" => "getAll", "first" => "getFirst", "last" => "getLast"
    ];

    /**
     * The logger
     * @var Logger
     */
    private $logger;

    /**
     * The type for logger
     */
    const TYPE = "COLLECTION";

    /**
     * Collection constructor.
     */
    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->logger->setType(self::TYPE);
    }

    /**
     * Get instance from array
     * @param array $firstitems
     */
    public static function from(array $firstItems = []): Collection
    {
        $collection = new self();
        $collection->items = $firstItems;

        return $collection;
    }

    /**
     * Check if the collection is empty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get the number of items
     * @return int
     */
    public function length(): int
    {
        return count($this->items);
    }

    /**
     * Get the sum of values
     * @param null $key
     * @return int
     */
    public function sum(string $key = null): int
    {
        $sum = 0;

        foreach ($this->items as $item) {

            if (is_array($item) && null != $key) {
                foreach ($item as $subKey => $subItem) {

                    if ($subKey == $key && is_int($subItem)) { $sum += $subItem; }

                }
            } elseif (is_int($item)) {
                $sum += $item;
            }

        }

        return $sum;
    }

    /**
     * Check if the collection contains the value
     * @param $value
     * @return bool
     */
    public function contains($value): bool
    {
        return in_array($value, $this->items);
    }

    /**
     * Check if the key exists in the collection
     * @param $key
     * @return bool
     */
    public function keyExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get all keys
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Add a new value in the collection with or without key
     * @param mixed $value
     * @param string|null $key
     * @return Collection
     * @throws CollectionException
     */
    public function add($value, string $key = null): Collection
    {
        if (null != $key) {

            if ($this->keyExists($key, $this->items)) {
                // Register log
                $this->logger->setLevel(Logger::LOG_ERROR);
                $this->logger->write("Key $key already added. Code : " . CollectionException::KEY_ALREADY_ADDED);

                throw new CollectionException("Key $key already added.", CollectionException::KEY_ALREADY_ADDED);
            } else {
                $this->items[$key] = $value;
            }

        } else {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Push items of the collection in this collection
     * Keep the old values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function push(Collection $collection): Collection
    {
        foreach ($collection->getAll() as $k => $v) {
            if (!$this->keyExists($k)) {
                $this->items[$k] = $v;
            }
        }

        return $this;
    }

    /**
     * Merge items of the collection with this collection
     * Replace the old values by the new values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function merge(Collection $collection): Collection
    {
        foreach ($collection->getAll() as $key => $value) {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a new value in the collection with or without key
     * @param string $key
     * @param mixed $value
     * @return Collection
     * @throws CollectionException
     */
    public function update($key, $value)
    {
        if ($this->keyExists($key, $this->items)) {
            $this->items[$key] = $value;
        } else {
            // Register log
            $this->logger->setLevel(Logger::LOG_ERROR);
            $this->logger->write("The key $key does not exist in the collection. Code : " . CollectionException::KEY_INVALID);

            throw new CollectionException("The key $key does not exist in the collection.", CollectionException::KEY_INVALID);
        }

        return $this;
    }

    /**
     * Get value of the collection with the key
     * @param string|int $key
     * @return mixed
     * @throws CollectionException
     */
    public function get($key)
    {
        if ($this->keyExists($key, $this->items)) {
            return $this->items[$key];
        } else {
            // Register log
            $this->logger->setLevel(Logger::LOG_ERROR);
            $this->logger->write("The key $key does not exist in the collection. Code : " . CollectionException::KEY_INVALID);

            throw new CollectionException("The key $key does not exist in the collection.", CollectionException::KEY_INVALID);
        }
    }

    /**
     * Get all items of the collection
     * @return array
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * Get the first item of the collection
     * @return mixed|null
     */
    public function getFirst()
    {
        if (empty($this->items)) { return null; }

        foreach ($this->items as $item) {
            return $item;
        }
    }

    /**
     * Get the last item of the collection
     * @return mixed
     */
    public function getLast()
    {
        return !empty($this->items) ? end($this->items) : null;
    }

    /**
     * Drop value by key or directly by value
     * @param mixed $keyOrValue
     * @return Collection
     * @throws CollectionException
     */
    public function drop($keyOrValue)
    {
        if ($this->keyExists($keyOrValue, $this->items)) {
            unset($this->items[$keyOrValue]);
        } elseif (in_array($keyOrValue, $this->items)) {

            foreach ($this->items as $key => $value) {
                if ($value == $keyOrValue) {
                    unset($this->items[$key]);
                }
            }

        } else {
            // Register log
            $this->logger->setLevel(Logger::LOG_ERROR);
            $this->logger->write("The key $keyOrValue does not exist in the collection. Code : " . CollectionException::KEY_INVALID);

            throw new CollectionException("The key $keyOrValue does not exist in the collection.", CollectionException::KEY_INVALID);
        }

        return $this;
    }

    /**
     * Erase a part of the collection
     * @param int $start
     * @param null|int $length
     * @return $this
     */
    public function slice($start, $length = null)
    {
        $this->items = array_slice($this->items, $start, $length);

        return $this;
    }

    /**
     * Reset collection
     * @return Collection
     */
    public function purge()
    {
        $this->items = [];

        return $this;
    }

    /**
     * Reverse the collection items
     * @return Collection
     */
    public function reverse()
    {
        return self::from( array_reverse($this->items, true) );
    }

    /**
     * Map the collection items
     * @param callable $callback
     * @return Collection
     */
    public function map($callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return self::from( array_combine($keys, $items) );
    }

    /**
     * Filter on the collection items
     * @param callable $callback
     * @return Collection
     */
    public function filter($callback)
    {
        $items = array_filter($this->items, $callback);

        return self::from($items);
    }

    /**
     * Call a method with the alias
     * @param $name
     * @param $args
     * @return mixed
     * @throws CollectionException
     */
    public function __call($name, $args)
    {
        if (array_key_exists($name, $this->alias)) {
            return call_user_func_array([$this, $this->alias[$name]], $args);
        }

        // Register log
        $this->logger->setLevel(Logger::LOG_ERROR);
        $this->logger->write("Method or alias $name does not exist. Code : " . CollectionException::METHOD_DOES_NOT_EXIST);

        throw new CollectionException("Method or alias $name does not exist.", CollectionException::METHOD_DOES_NOT_EXIST);
    }
}
