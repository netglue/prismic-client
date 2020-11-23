<?php

declare(strict_types=1);

namespace Prismic\Exception;

use function sprintf;

class UnknownForm extends InvalidArgument
{
    public static function withName(string $name): self
    {
        return new static(sprintf(
            'There is no form available with the name %s',
            $name
        ));
    }
}
