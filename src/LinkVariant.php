<?php

declare(strict_types=1);

namespace Prismic;

interface LinkVariant
{
    /** @return non-empty-string|null */
    public function variant(): string|null;
}
