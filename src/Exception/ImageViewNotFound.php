<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Prismic\Document\Fragment\Image;

use function implode;
use function sprintf;

final class ImageViewNotFound extends InvalidArgument
{
    public static function withNameAndImage(string $name, Image $image): self
    {
        return new self(sprintf(
            'The image view "%s" does not exist. Known view names are: %s',
            $name,
            implode(', ', $image->knownViews())
        ));
    }
}
