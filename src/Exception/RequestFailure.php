<?php
declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

class RequestFailure extends RuntimeException implements PrismicError
{
    public static function withClientException(ClientExceptionInterface $exception) : self
    {
        return new static(
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }
}
