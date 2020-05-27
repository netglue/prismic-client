<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use function array_filter;

class Collection extends BaseCollection
{
    public function slicesOfType(string $type) : self
    {
        return self::new(array_filter($this->fragments, static function (Fragment $fragment) use ($type) : bool {
            return $fragment instanceof Slice && $fragment->type() === $type;
        }));
    }
}
