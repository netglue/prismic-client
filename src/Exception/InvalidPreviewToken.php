<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Message\UriInterface;
use Throwable;

use function sprintf;

final class InvalidPreviewToken extends InvalidArgument
{
    public static function mismatchedPreviewHost(UriInterface $apiUri, UriInterface $previewUri): self
    {
        return new self(sprintf(
            'The preview url has been rejected because its host name "%s" does not match the api host "%s"',
            $previewUri->getHost(),
            $apiUri->getHost(),
        ));
    }

    public static function withInvalidUrl(Throwable $error): self
    {
        return new self('The given preview token is not a valid url', 400, $error);
    }
}
