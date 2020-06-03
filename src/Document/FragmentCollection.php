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

    /**
     * Whether a fragment exists in the collection with the given offset
     *
     * @param int|string $name
     */
    public function has($name) : bool;

    /**
     * Return the fragment at the given offset, or an empty fragment
     *
     * @param int|string $name
     */
    public function get($name) : Fragment;

    /**
     * Return the first fragment found in the collection
     *
     * If the collection is empty, this method will return an @link EmptyFragment
     */
    public function first() : Fragment;

    /**
     * Return the last fragment found in the collection
     *
     * If the collection is empty, this method will return an @link EmptyFragment
     */
    public function last() : Fragment;
}
