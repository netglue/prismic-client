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

    public function has(string $name) : bool;

    public function get(string $name) : Fragment;

    /** @param int|string $index */
    public function offsetGet($index) :? Fragment;

    /** @param int|string $index */
    public function offsetExists($index) : bool;
}
