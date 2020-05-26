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
}
