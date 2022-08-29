<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\EmptyFragment;
use Prismic\Document\Fragment\StringFragment;
use PrismicTest\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testEmptyCollectionReturnsEmptyFragmentForFirstAndLast(): void
    {
        $collection = Collection::new([]);
        $this->assertInstanceOf(EmptyFragment::class, $collection->first());
        $this->assertInstanceOf(EmptyFragment::class, $collection->last());
    }

    public function testAnEmptyCollectionIsConsideredEmpty(): void
    {
        $this->assertTrue(
            Collection::new([])->isEmpty(),
        );
    }

    public function testCollectionOfEmptyFragmentsIsConsideredEmpty(): void
    {
        $collection = Collection::new([
            new EmptyFragment(),
            StringFragment::new(''),
        ]);

        $this->assertTrue($collection->isEmpty());
    }

    public function testACollectionWithNonEmptyFragmentIsNotConsideredEmpty(): void
    {
        $this->assertFalse(
            Collection::new([StringFragment::new('Foo')])->isEmpty(),
        );
    }

    public function testThatFirstAndLastReturnTheExpectedValues(): void
    {
        $list = [
            'a' => StringFragment::new('a'),
            'b' => StringFragment::new('b'),
            'c' => StringFragment::new('c'),
            'd' => StringFragment::new('d'),
        ];
        $collection = Collection::new($list);
        $this->assertSame($list['a'], $collection->first());
        $this->assertSame($list['d'], $collection->last());
    }

    public function testThatGetWithUnknownNameWillReturnEmptyFragment(): void
    {
        $collection = Collection::new([]);
        $this->assertFalse($collection->has('not-there'));
        $this->assertInstanceOf(EmptyFragment::class, $collection->get('not-there'));
    }

    public function testThatGetWithUnknownIndexWillReturnEmptyFragment(): void
    {
        $collection = Collection::new([]);
        $this->assertFalse($collection->has(0));
        $this->assertInstanceOf(EmptyFragment::class, $collection->get(0));
    }

    public function testThatFilteringReIndexesNumericKeys(): void
    {
        $string = StringFragment::new('a');
        $collection = Collection::new([
            new EmptyFragment(),
            $string,
            new EmptyFragment(),
        ]);

        $this->assertSame($string, $collection->get(1));
        $filtered = $collection->nonEmpty();
        $this->assertSame($string, $filtered->get(0));
    }

    public function testThatFilteringDoesNotAlterStringKeys(): void
    {
        $string = StringFragment::new('a');
        $collection = Collection::new([
            'a' => new EmptyFragment(),
            'b' => $string,
            'c' => new EmptyFragment(),
        ]);

        $this->assertSame($string, $collection->get('b'));
        $this->assertCount(3, $collection);
        $filtered = $collection->nonEmpty();
        $this->assertSame($string, $filtered->get('b'));
        $this->assertCount(1, $filtered);
    }
}
