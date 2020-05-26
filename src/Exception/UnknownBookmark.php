<?php
declare(strict_types=1);

namespace Prismic\Exception;

use function sprintf;

class UnknownBookmark extends InvalidArgument
{
    public static function withName(string $name) : self
    {
        return new static(sprintf(
            'There is no bookmark available with the name %s',
            $name
        ));
    }
}
