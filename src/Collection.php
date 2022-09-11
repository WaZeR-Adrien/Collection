<?php declare(strict_types=1);

namespace AdrienM\Collection;

use AdrienM\Logger\LogException;
use AdrienM\Logger\Logger;
use function _HumbugBoxf43f7c5c5350\str_contains;

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
     * Get instance from initial collection
     * @param Collection $initialCol
     * @return Collection
     */
    public static function from(Collection $initialCol): self
    {
        $collection = new self();
        $collection->items = $initialCol->getAll();

        return $collection;
    }

    /**
     * Get instance from array
     * @param array<mixed> $firstItems
     * @return Collection
     */
    public static function of(array $firstItems = []): self
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
     * @param string|int|null $key
     * @return int
     */
    public function sum($key = null): int
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
    public function contains($value): bool
    {
        return in_array($value, $this->items);
    }

    /**
     * Check if the collection contains the value
     * @param string $reg
     * @return bool
     */
    public function containsWithRegex(string $reg): bool
    {
        return $this->find(function ($item) use ($reg) {
            return is_string($item) && preg_match($reg, $item);
        }) ? true : false;
    }

    /**
     * Check if the collection contains the value
     * @param string $string
     * @return bool
     */
    public function containsString(string $string): bool
    {
        return $this->find(function ($item) use ($string) {
            return is_string($item) && is_numeric(strpos($item, $string));
        }) ? true : false;
    }

    /**
     * Check if the key exists in the collection
     * @param string|int $key
     * @return bool
     */
    public function keyExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get all keys
     * @return string[]|int[]
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * Add a new value in the collection with or without key
     * @param mixed $value
     * @param string|int|null $key
     * @return Collection
     * @throws CollectionException
     * @throws LogException
     */
    public function add($value, $key = null): self
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
     * Push items of the parameter collection in this collection
     * Keep the old values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function push(Collection $collection): self
    {
        foreach ($collection->getAll() as $k => $v) {
            if (!$this->keyExists($k)) {
                $this->items[$k] = $v;
            }
        }

        return $this;
    }

    /**
     * Push only values of the parameter collection in this collection
     * Don't push keys of the parameter collection
     * @param Collection $collection
     * @return Collection
     */
    public function pushOnlyValues(Collection $collection): self
    {
        array_push($this->items, ...$collection->getAll());

        return $this;
    }

    /**
     * Merge items of the collection with this collection
     * Replace the old values by the new values if there are the same key in collections
     * @param Collection $collection
     * @return Collection
     */
    public function merge(Collection $collection): self
    {
        foreach ($collection->getAll() as $key => $value) {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Flatten items of the multidimentionnal collection
     * @return Collection
     */
    public function flatten(): self
    {
        $col = new Collection();
        foreach ($this->getAll() as $key => $value) {
            if ($value instanceof Collection) {
                $col->merge($value->flatten());
            } elseif (is_array($value)) {
                $col->merge(Collection::of($value)->flatten());
            } else {
                $col->merge(Collection::of([$key => $value]));
            }
        }

        return $col;
    }

    /**
     * Add a new value in the collection with or without key
     * @param string|int $key
     * @param mixed $value
     * @return Collection
     * @throws CollectionException
     * @throws LogException
     */
    public function replace($key, $value): self
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
     * @throws LogException
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
        $items = array_slice($this->items, 0, 1);

        return !empty($items) ? $items[0] : null;
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
     * Find the first item with callback
     * @param callable $callback
     * @return mixed|null
     */
    public function find(callable $callback)
    {
        foreach ($this->items as $key => $value) {
            if ($callback($value, $key, $this->items)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Drop value by key or directly by value
     * @param mixed $keyOrValue
     * @return Collection
     * @throws CollectionException
     * @throws LogException
     */
    public function drop($keyOrValue): self
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
    public function slice(int $start, int $length = null): self
    {
        $this->items = array_slice($this->items, $start, $length);

        return $this;
    }

    /**
     * Reset collection
     * @return Collection
     */
    public function purge(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * Reverse the collection items
     * Return a copy of this collection with reversed items
     * @return Collection
     */
    public function reverse(): self
    {
        return self::of( array_reverse($this->items, true) );
    }

    /**
     * Map the collection items
     * Return a copy of this collection with results of the callback
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback): self
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return self::of( array_combine($keys, $items) );
    }

    /**
     * Filter on the collection items
     * Return a copy of this collection with filtered items
     * @param callable $callback
     * @return Collection
     */
    public function filter(callable $callback): self
    {
        $items = array_filter($this->items, $callback);

        return self::of($items);
    }

    /**
     * Join array of string by glue
     * @param string $glue
     * @return string
     */
    public function join(string $glue): string
    {
        return implode($glue, $this->items);
    }

    /**
     * Call a method with the alias
     * @param string $name
     * @param array<mixed> $args
     * @return mixed
     * @throws CollectionException
     * @throws LogException
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
