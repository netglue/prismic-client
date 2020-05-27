<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use Closure;
use Prismic\Document\Fragment;
use Prismic\Document\FragmentCollection;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;
use function count;
use function end;
use function reset;
use const ARRAY_FILTER_USE_BOTH;

abstract class BaseCollection implements Fragment, FragmentCollection
{
    /** @var Fragment[] */
    protected $fragments;

    /** @param Fragment[] $fragments */
    protected function __construct(iterable $fragments)
    {
        $this->fragments = [];
        foreach ($fragments as $name => $fragment) {
            $this->addFragment($fragment, $name);
        }
    }

    /** @param Fragment[] $fragments */
    public static function new(iterable $fragments) : self
    {
        return new static($fragments);
    }

    /** @param int|string|null $key */
    final protected function addFragment(Fragment $fragment, $key = null) : void
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

    public function first() : Fragment
    {
        if (! $this->count()) {
            return new EmptyFragment();
        }

        return reset($this->fragments);
    }

    public function last() : Fragment
    {
        if (! $this->count()) {
            return new EmptyFragment();
        }

        return end($this->fragments);
    }

    /** @return static */
    public function filter(Closure $p)
    {
        $result = array_filter($this->fragments, $p, ARRAY_FILTER_USE_BOTH);

        return new static(
            $this->isHash($result) ? $result : array_values($result)
        );
    }

    /** @param mixed[] $value */
    private function isHash(iterable $value) : bool
    {
        return count(array_filter(array_keys($value), '\is_string')) > 0;
    }

    public function has(string $name) : bool
    {
        return isset($this->fragments[$name]);
    }

    public function get(string $name) : Fragment
    {
        if (! $this->fragments[$name] instanceof Fragment) {
            return new EmptyFragment();
        }

        return $this->fragments[$name];
    }

    /** @inheritDoc */
    public function offsetExists($index) : bool
    {
        return array_key_exists($index, $this->fragments);
    }

    /** @inheritDoc */
    public function offsetGet($index) :? Fragment
    {
        return $this->fragments[$index] ?? null;
    }
}
