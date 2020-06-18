<?php
declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

use const PHP_EOL;

class AuthenticationError extends RequestFailure
{
    public static function with(RequestInterface $request, ResponseInterface $response) : self
    {
        $url = sprintf('%s?%s', $request->getUri()->getPath(), $request->getUri()->getQuery());
        $error = new static(sprintf(
            '%d %s: Authentication failed for the api host "%s" and the url "%s"' . PHP_EOL .
            'Either a token is required and not present, or an invalid token was provided',
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $request->getUri()->getHost(),
            $url
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }
}
