<?php declare(strict_types=1);

namespace AdrienM\Collection;

use AdrienM\Logger\Logger;

/**
 * @method int count()
 * @method int size()
 * @method array all()
 * @method mixed|null first()
 * @method mixed|null last()
 */
class Collection
{
    /**
     * Items of the collection
     * @var array<mixed>
     */
    private $items = [];

    /**
     * All alias to call the good method
     * @var array<string>
     */
    private $alias = [
        "count" => "length", "size" => "length", "all" => "getAll", "first" => "getFirst", "last" => "getLast"
    ];

    /**
     * The logger
     * @var Logger
     */
    private $logger;

    /**
     * The type for logger
     */
    private const TYPE = "COLLECTION";

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
     * @param array<mixed> $firstItems
     * @return Collection
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
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the number of items
     * @return int
     */
    public function length()
    {
        return count($this->items);
    }

    /**
     * Get the sum of values
     * @param string|null $key
     * @return int
     */
    public function sum(?string $key = null)
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
     * @param mixed $value
     * @return bool
     */
    public function contains($value)
    {
        return in_array($value, $this->items);
    }

    /**
     * Check if the key exists in the collection
     * @param string|int $key
     * @return bool
     */
    public function keyExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get all keys
     * @return array<string|int>
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
    public function add($value, string $key = null)
    {
        if (null != $key) {

            if ($this->keyExists($key)) {
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
    public function push(Collection $collection)
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
    public function merge(Collection $collection)
    {
        foreach ($collection->getAll() as $key => $value) {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a new value in the collection with or without key
     * @param string|int $key
     * @param mixed $value
     * @return Collection
     * @throws CollectionException
     */
    public function replace($key, $value)
    {
        if ($this->keyExists($key)) {
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
     * @throws \AdrienM\Logger\LogException
     */
    public function get($key)
    {
        if ($this->keyExists($key)) {
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
     * @return array<mixed>
     */
    public function getAll(): array
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
     * @return mixed|null
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
        if ($this->keyExists($keyOrValue)) {
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
     * @return Collection
     */
    public function slice(int $start, int $length = null)
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
     * @param string $name
     * @param array<mixed> $args
     * @return mixed
     * @throws CollectionException
     * @throws \AdrienM\Logger\LogException
     */
    public function __call(string $name, array $args)
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
