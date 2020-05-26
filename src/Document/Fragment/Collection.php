<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use function array_filter;

class Collection extends BaseCollection
{
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

    public function slicesOfType(string $type) : self
    {
        return self::new(array_filter($this->fragments, static function (Fragment $fragment) use ($type) : bool {
            return $fragment instanceof Slice && $fragment->type() === $type;
        }));
    }
}
