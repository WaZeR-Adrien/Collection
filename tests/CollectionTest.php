<?php
namespace Tests;

use AdrienM\Collection\Collection;
use AdrienM\Collection\CollectionException;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @before
     */
    public function setupInstance(): void
    {
        $this->collection = new Collection();
    }

    public function tearDown(): void
    {
        $this->collection->purge();
        $this->testIsEmpty();
    }

    /**
     * Test if the collection is empty
     */
    public function testIsEmpty(): void
    {
        self::assertTrue($this->collection->isEmpty(), "The collection must be empty");
    }

    /**
     * Test drop item
     */
    public function testDrop(): void
    {
        $this->collection
            ->add("foo")
            ->add("foo2");

        self::assertSame(
            ["foo", "foo2"], $this->collection->getAll(),
            "The collection must contain ['foo', 'foo2'] values");

        $this->collection->drop(1); // by key : 1 => "foo2"

        self::assertSame(
            ["foo"], $this->collection->getAll(),
            "The collection must contain ['foo'] values");

        $this->collection->drop("foo"); // by value : 0 => "foo"

        self::assertTrue($this->collection->isEmpty(), "The collection must be empty");
    }

    /**
     * Test the length of the collection after being added
     */
    public function testLength(): void
    {
        $this->collection->add("first");
        self::assertSame(1, $this->collection->length(), "The collection must contain one value");

        $this->collection->drop("first");
        self::assertSame(0, $this->collection->length(), "The collection must contain no value");
    }

    /**
     * Test the sum of the values
     */
    public function testSum(): void
    {
        $this->collection
            ->add(10)
            ->add(15)
            ->add(5);

        self::assertSame(30, $this->collection->sum(), "The collection must contain an sum of 30");
    }

    /**
     * Test the sum of the values
     */
    public function testSumWithSubValues(): void
    {
        $this->collection
            ->add([
                "foo" => 10,
                "bar" => 20
            ])
            ->add([
                "foo" => 1,
                "bar" => 5
            ]);

        self::assertSame(11, $this->collection->sum("foo"), "The collection must contain an sum of 11 for 'foo' key");
        self::assertSame(25, $this->collection->sum("bar"), "The collection must contain an sum of 25 for 'bar' key");
    }

    /**
     * Test if the collection contain "foo" value and "bar" key
     */
    public function testContainsValueAndKey(): void
    {
        $this->collection->add("foo", "bar");

        self::assertTrue($this->collection->contains("foo"), "The collection must contain the value 'foo'");
        self::assertTrue($this->collection->keyExists("bar"), "The collection must contain the key 'bar'");
    }

    /**
     * Test get all keys
     */
    public function testGetKeys(): void
    {
        $this->collection
            ->add("foo", "bar")
            ->add("foo2", "bar2");

        self::assertSame(["bar", "bar2"], $this->collection->keys(), "The collection must contain ['bar', 'bar2'] keys");
    }


    /**
     * Test get all values
     */
    public function testGetAll(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar");

        self::assertSame(["foo", "bar"], $this->collection->getAll(), "The collection must contain ['foo', 'bar'] values");
    }

    /**
     * Test if the first value is "foo"
     */
    public function testGetFirst(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar");

        self::assertSame("foo", $this->collection->getFirst(), "The first value must be 'foo'");
        self::assertSame("foo", $this->collection->first(), "The first value must be 'foo' with the alias 'first'");
    }

    /**
     * Test if there are no first value
     */
    public function testNullGetFirst(): void
    {
        self::assertSame(null, $this->collection->getFirst(), "There must be no value");
        self::assertSame(null, $this->collection->first(), "There must be no value");
    }

    /**
     * Test if the last value is "bar"
     */
    public function testGetLast(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar");

        self::assertSame("bar", $this->collection->getLast(), "The last value must be 'bar'");
        self::assertSame("bar", $this->collection->last(), "The last value must be 'bar' with the alias 'last'");
    }

    /**
     * Test if there are no last value
     */
    public function testNullGetLast(): void
    {
        self::assertSame(null, $this->collection->getLast(), "There must be no value");
        self::assertSame(null, $this->collection->last(), "There must be no value");
    }

    /**
     * Test to find item
     */
    public function testFind(): void
    {
        $this->collection
            ->add("a")
            ->add("b")
            ->add("c")
            ->add("d");

        self::assertSame(["a", "b", "c", "d"], $this->collection->getAll(), "The collection must contain ['a', 'b', 'c', 'd'] values");

        $itemFinded = $this->collection->find(function ($value) {
            return $value === "b";
        });

        self::assertSame("b", $itemFinded, "The value must be 'd'");

        $itemFinded = $this->collection->find(function ($value) {
            return $value === "x";
        });

        self::assertSame(null, $itemFinded, "There must be no value");
    }

    /**
     * Test to edit values
     */
    public function testEdit(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar");

        self::assertSame("foo", $this->collection->getFirst(), "The first value must be 'foo' before being updated");

        $this->collection->replace(0, "fooUpdated");
        self::assertSame("fooUpdated", $this->collection->getFirst(), "The first value must be 'fooUpdated' after being updated");
    }

    /**
     * Test to push collection
     */
    public function testPush(): void
    {
        $this->collection
            ->add("foo", "key")
            ->add("bar", "key2");

        self::assertSame(["key" => "foo", "key2" => "bar"], $this->collection->getAll(), "The collection must contain ['key' => 'foo', 'key2' => 'bar'] values");

        $this->collection->push(Collection::from(["key3" => "test"]));
        self::assertSame(
            ["key" => "foo", "key2" => "bar", "key3" => "test"], $this->collection->getAll(),
            "The collection must contain ['key' => 'foo', 'key2' => 'bar', 'key3' => 'test'] values");
    }

    /**
     * Test to push collection
     */
    public function testPushOnlyValues(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar");

        self::assertSame(["foo", "bar"], $this->collection->getAll(), "The collection must contain ['foo', 'bar'] values");

        $this->collection->pushOnlyValues(Collection::from(["test"]));
        self::assertSame(["foo", "bar", "test"], $this->collection->getAll(), "The collection must contain ['foo', 'bar', 'test'] values");
    }

    /**
     * Test to merge collections
     */
    public function testMerge(): void
    {
        $this->collection
            ->add("foo", "key")
            ->add("bar", "key2");


        self::assertSame(["key" => "foo", "key2" => "bar"], $this->collection->getAll(), "The collection must contain ['key' => 'foo', 'key2' => 'bar'] values");

        $this->collection->merge(Collection::from(["key" => "test"]));
        self::assertSame(["key" => "test", "key2" => "bar"], $this->collection->getAll(), "The collection must contain ['key' => 'test', 'key2' => 'bar'] values");
    }

    /**
     * Test to get a specific value
     */
    public function testGetValue(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar")
            ->add("foo2", "bar2");

        self::assertSame("foo", $this->collection->get(0), "The first value must be 'food'");
        self::assertSame("bar", $this->collection->get(1), "The second value must be 'bar'");
        self::assertSame("foo2", $this->collection->get("bar2"), "The third value must be 'foo2'");
    }

    /**
     * Test to slice collection
     */
    public function testSlice(): void
    {
        $this->collection
            ->add("foo")
            ->add("bar")
            ->add("foo2")
            ->add("bar2");

        self::assertSame(["foo", "bar", "foo2", "bar2"], $this->collection->getAll(), "The collection must contain ['foo', 'bar', 'foo2', 'bar2'] values");

        $this->collection->slice(1, 2);
        self::assertSame(["bar", "foo2"], $this->collection->getAll(), "The collection must contain ['bar', 'foo2'] values");

        $this->collection->slice(1);
        self::assertSame(["foo2"], $this->collection->getAll(), "The collection must contain ['foo2'] values");
    }

    /**
     * Test to reverse items
     */
    public function testReverse(): void
    {
        $this->collection
            ->add("foo", "bar")
            ->add("foo2", "bar2")
            ->add("foo3", "bar3")
            ->add("foo4", "bar4");

        self::assertSame(
            ["bar" => "foo", "bar2" => "foo2", "bar3" => "foo3", "bar4" => "foo4"],
            $this->collection->getAll(),
            "The collection must contain ['bar' => 'foo', 'bar2' => 'foo2', 'bar3' => 'foo3', 'bar4' => 'foo4'] values");

        $collectionReversed = $this->collection->reverse();
        self::assertSame(
            ["bar4" => "foo4", "bar3" => "foo3", "bar2" => "foo2", "bar" => "foo"],
            $collectionReversed->getAll(),
            "The collection must contain ['bar4' => 'foo4', 'bar3' => 'foo3', 'bar2' => 'foo2', 'bar' => 'foo'] values");
    }

    /**
     * Test filter on items
     */
    public function testFilter(): void
    {
        $this->collection
            ->add(15)
            ->add(20)
            ->add(25)
            ->add(30);

        self::assertSame(
            [15, 20, 25, 30], $this->collection->getAll(),
            "The collection must contain [15, 20, 25, 30] values");

        $collectionFiltered = $this->collection->filter(function (int $item) {
            return $item % 2 === 0;
        });

        self::assertSame(
            [1 => 20, 3 => 30], $collectionFiltered->getAll(),
            "The collection must contain [1 => 20, 3 =>30] values");
    }

    /**
     * Test map on items
     */
    public function testMap(): void
    {
        $this->collection
            ->add("foo", "bar")
            ->add("foo2", "bar2");

        self::assertSame(
            ["bar" => "foo", "bar2" => "foo2"], $this->collection->getAll(),
            "The collection must contain ['bar' => 'foo', 'bar2' => 'foo2'] values");

        $collectionUpdated = $this->collection->map(function (string $item) {
            return strtoupper($item) . "Updated";
        });

        self::assertSame(
            ["bar" => "FOOUpdated", "bar2" => "FOO2Updated"], $collectionUpdated->getAll(),
            "The collection must contain ['bar' => 'FOOUpdated', 'bar2' => 'FOO2Updated'] values");
    }

    /**
     * Test to get a specific value
     */
    public function testGetException(): void
    {
        try {
            $this->collection->get("foobar");
        } catch (CollectionException $e) {
            self::assertSame(501, $e->getCode(), "There must generate an CollectionException");
        }
    }

    /**
     * Test to get a specific value
     */
    public function testReplaceException(): void
    {
        try {
            $this->collection->replace("foobar", 10);
        } catch (CollectionException $e) {
            self::assertSame(501, $e->getCode(), "There must generate an CollectionException");
        }
    }

    /**
     * Test exception of methods
     */
    public function testDropException(): void
    {
        try {
            $this->collection->drop("foobar");
        } catch (CollectionException $e) {
            self::assertSame(501, $e->getCode(), "There must generate an CollectionException");
        }
    }

    /**
     * Test exception of methods
     */
    public function testAddException(): void
    {
        $this->collection->add("value", "key");

        try {
            $this->collection->add("value2", "key");
        } catch (CollectionException $e) {
            self::assertSame(500, $e->getCode(), "There must generate an CollectionException");
        }
    }

    public function testUnknownMethodException(): void
    {
        try {
            $this->collection->unknown();
        } catch (CollectionException $e) {
            self::assertSame(502, $e->getCode(), "There must generate an CollectionException");
        }
    }

}
