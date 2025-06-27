<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use Closure;
use Override;
use Prismic\Document\Fragment;
use Prismic\Document\FragmentCollection;
use Stringable;
use Traversable;

use function array_filter;
use function array_keys;
use function array_values;
use function assert;
use function count;
use function end;
use function implode;
use function reset;

use const ARRAY_FILTER_USE_BOTH;
use const PHP_EOL;

/**
 * @template T of Fragment
 * @implements FragmentCollection<T>
 * @psalm-consistent-constructor
 */
abstract class BaseCollection implements FragmentCollection
{
    /**
     * @var array<array-key, T>
     * @phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    protected $fragments;

    /** @param iterable<array-key, T> $fragments */
    protected function __construct(iterable $fragments)
    {
        $this->fragments = [];
        foreach ($fragments as $name => $fragment) {
            $this->addFragment($fragment, $name);
        }
    }

    /**
     * @param iterable<array-key, T2> $fragments
     *
     * @return static<T2>
     *
     * @template T2 of Fragment
     */
    public static function new(iterable $fragments): self
    {
        return new static($fragments);
    }

    /**
     * @param T              $fragment
     * @param array-key|null $key
     */
    final protected function addFragment(Fragment $fragment, int|string|null $key = null): void
    {
        if ($key !== null) {
            $this->fragments[$key] = $fragment;

            return;
        }

        $this->fragments[] = $fragment;
    }

    /** @return Traversable<array-key, T> */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fragments);
    }

    #[Override]
    public function count(): int
    {
        return count($this->fragments);
    }

    #[Override]
    public function isEmpty(): bool
    {
        if ($this->count() === 0) {
            return true;
        }

        foreach ($this as $fragment) {
            if (! $fragment->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function first(): Fragment
    {
        if (! $this->count()) {
            /** @psalm-var T */
            return new EmptyFragment();
        }

        $first = reset($this->fragments);
        assert($first instanceof Fragment);

        return $first;
    }

    #[Override]
    public function last(): Fragment
    {
        if (! $this->count()) {
            /** @psalm-var T */
            return new EmptyFragment();
        }

        $last = end($this->fragments);
        assert($last instanceof Fragment);
        reset($this->fragments);

        return $last;
    }

    #[Override]
    public function filter(Closure $p): self
    {
        $result = array_filter($this->fragments, $p, ARRAY_FILTER_USE_BOTH);

        return new static(
            $this->isHash($result) ? $result : array_values($result),
        );
    }

    /** @param array<array-key, Fragment> $value */
    private function isHash(array $value): bool
    {
        return count(array_filter(array_keys($value), '\is_string')) > 0;
    }

    /** @inheritDoc */
    #[Override]
    public function has($name): bool
    {
        return isset($this->fragments[$name]);
    }

    /** @inheritDoc */
    #[Override]
    public function get($name): Fragment
    {
        if (! $this->has($name)) {
            return new EmptyFragment();
        }

        return $this->fragments[$name];
    }

    public function nonEmpty(): self
    {
        return $this->filter(static function (Fragment $fragment): bool {
            return ! $fragment->isEmpty();
        });
    }

    public function __toString(): string
    {
        $buffer = [];

        foreach ($this as $fragment) {
            if (! $fragment instanceof Stringable) {
                continue;
            }

            /** @psalm-var Fragment&Stringable $fragment */

            $buffer[] = (string) $fragment;
        }

        return implode(PHP_EOL, $buffer);
    }
}
