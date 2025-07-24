<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;
use function str_contains;

final class ResponseTooLarge extends RequestFailure
{
    public static function matches(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() !== 422) {
            return false;
        }

        return str_contains(
            (string) $response->getBody(),
            'maximum size of response body',
        );
    }

    public static function with(RequestInterface $request, ResponseInterface $response): self
    {
        $error = new self(
            sprintf(
                'Your request was rejected because the response would be too large. Error message: %s',
                (string) $response->getBody(),
            ),
            $response->getStatusCode(),
        );
        $error->response = $response;
        $error->request = $request;

        return $error;
    }
}
