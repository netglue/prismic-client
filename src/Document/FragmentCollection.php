<?php

declare(strict_types=1);

namespace Prismic\Document;

use Closure;
use Countable;
use IteratorAggregate;
use Stringable;

/**
 * @template-covariant T of Fragment
 * @template-extends IteratorAggregate<array-key, T>
 * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
 * @phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
 * @todo Add native parameter and return type hints in 2.0.0
 */
interface FragmentCollection extends Fragment, IteratorAggregate, Countable, Stringable
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
    public function has($name): bool;

    /**
     * Return the fragment at the given offset, or an empty fragment
     *
     * @param int|string $name
     */
    public function get($name): Fragment;

    /**
     * Return the first fragment found in the collection
     *
     * If the collection is empty, this method will return an @link EmptyFragment
     *
     * @psalm-return T
     */
    public function first(): Fragment;

    /**
     * Return the last fragment found in the collection
     *
     * If the collection is empty, this method will return an @link EmptyFragment
     *
     * @psalm-return T
     */
    public function last(): Fragment;
}
