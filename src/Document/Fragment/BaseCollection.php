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
 * @template-implements FragmentCollection<Fragment>
 * @psalm-consistent-constructor
 */
abstract class BaseCollection implements FragmentCollection
{
    /**
     * @var array<array-key, Fragment>
     * @phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    protected $fragments;

    /** @param iterable<array-key, Fragment> $fragments */
    protected function __construct(iterable $fragments)
    {
        $this->fragments = [];
        foreach ($fragments as $name => $fragment) {
            $this->addFragment($fragment, $name);
        }
    }

    /**
     * @param iterable<array-key, Fragment> $fragments
     *
     * @return static
     */
    public static function new(iterable $fragments): self
    {
        return new static($fragments);
    }

    final protected function addFragment(Fragment $fragment, int|string|null $key = null): void
    {
        if ($key !== null) {
            $this->fragments[$key] = $fragment;

            return;
        }

        $this->fragments[] = $fragment;
    }

    /** @return Traversable<array-key, Fragment> */
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
            return new EmptyFragment();
        }

        $last = end($this->fragments);
        assert($last instanceof Fragment);
        reset($this->fragments);

        return $last;
    }

    /**
     * @psalm-param Closure(mixed, ?array-key): bool $p
     *
     * @return static
     */
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

            $buffer[] = (string) $fragment;
        }

        return implode(PHP_EOL, $buffer);
    }
}
