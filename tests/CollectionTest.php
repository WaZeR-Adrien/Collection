<?php
namespace Tests;

use AdrienM\Collection\Collection;
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

    /**
     * Test if the collection is empty
     */
    public function testIsEmpty(): void
    {
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->update(0, "fooUpdated");
        self::assertSame("fooUpdated", $this->collection->getFirst(), "The first value must be 'fooUpdated' after being updated");

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
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

        $this->collection->purge();
        $this->testIsEmpty();
    }
}
