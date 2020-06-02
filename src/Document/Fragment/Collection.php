<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

class Collection extends BaseCollection
{
    public function slicesOfType(string $type) : self
    {
        return $this->filter(static function (Fragment $fragment) use ($type) : bool {
            return $fragment instanceof Slice && $fragment->type() === $type;
        });
    }
}
