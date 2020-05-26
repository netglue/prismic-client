<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Prismic\Document\Fragment;
use function count;

abstract class BaseCollection implements Fragment, IteratorAggregate, Countable
{
    /** @var Fragment[] */
    protected $fragments;

    /** @param Fragment[] $fragments */
    protected function __construct(iterable $fragments)
    {
        $this->fragments = [];
        foreach ($fragments as $name => $fragment) {
            $this->addFragment($name, $fragment);
        }
    }

    /** @param Fragment[] $fragments */
    public static function new(iterable $fragments) : self
    {
        return new static($fragments);
    }

    /** @param int|string $key */
    protected function addFragment($key, Fragment $fragment) : void
    {
        $this->fragments[$key] = $fragment;
    }

    /** @return Fragment[] */
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->fragments);
    }

    public function count() : int
    {
        return count($this->fragments);
    }
}
