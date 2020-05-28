<?php
declare(strict_types=1);

namespace Prismic\Document;

use Closure;
use Countable;
use IteratorAggregate;

interface FragmentCollection extends Fragment, IteratorAggregate, Countable
{
    /**
     * Return a new collection by filtering with the given closure
     *
     * @return static
     */
    public function filter(Closure $p);

    /** @param int|string $name */
    public function has($name) : bool;

    /** @param int|string $name */
    public function get($name) : Fragment;

    /** @param int|string $index */
    public function offsetGet($index) : Fragment;

    /** @param int|string $index */
    public function offsetExists($index) : bool;
}
