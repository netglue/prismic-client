<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Stringable;

final class EmptyFragment implements Fragment, Stringable
{
    public function isEmpty(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return '';
    }
}
