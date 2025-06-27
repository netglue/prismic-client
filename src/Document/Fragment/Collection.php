<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

/**
 * @template T of Fragment
 * @extends BaseCollection<T>
 * @final This class will become hard-final in the next major (2.0)
 */
class Collection extends BaseCollection
{
    public function slicesOfType(string $type): self
    {
        return $this->filter(static function (Fragment $fragment) use ($type): bool {
            return $fragment instanceof Slice && $fragment->type() === $type;
        });
    }
}
