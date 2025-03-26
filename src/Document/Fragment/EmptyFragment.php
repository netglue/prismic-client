<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Stringable;

final class EmptyFragment implements Fragment, Stringable
{
    #[Override]
    public function isEmpty(): bool
    {
        return true;
    }

    #[Override]
    public function __toString(): string
    {
        return '';
    }
}
