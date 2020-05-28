<?php
declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use function sprintf;
use const PHP_EOL;

class RequestFailure extends RuntimeException implements PrismicError
{
    /** @var RequestInterface|null */
    protected $request;

    /** @var ResponseInterface|null */
    protected $response;

    public static function withClientException(ClientExceptionInterface $exception) : self
    {
        return new static(
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }

    public static function withRedirectResponse(RequestInterface $request, ResponseInterface $response) : self
    {
        $url = sprintf('%s?%s', $request->getUri()->getPath(), $request->getUri()->getQuery());
        $error = new static(sprintf(
            'The request to the URL "%s" resulted in a %d redirect. I don’t know what to do with that.',
            $url,
            $response->getStatusCode()
        ));
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public static function withClientError(RequestInterface $request, ResponseInterface $response) : self
    {
        $status = $response->getStatusCode();
        if ($status === 401) {
            return self::with401($request, $response);
        }

        $url = sprintf('%s?%s', $request->getUri()->getPath(), $request->getUri()->getQuery());
        $error = new static(sprintf(
            'Error %d. The request to the URL "%s" was rejected by the api. The error response body was %s',
            $status,
            $url,
            (string) $response->getBody()
        ));
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public static function with401(RequestInterface $request, ResponseInterface $response) : self
    {
        $url = sprintf('%s?%s', $request->getUri()->getPath(), $request->getUri()->getQuery());
        $error = new static(sprintf(
            'Authentication failed for the api host "%s" and the url "%s"' . PHP_EOL .
            'Either a token is required and not present, or an invalid token was provided',
            $request->getUri()->getHost(),
            $url
        ));
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public static function withServerError(RequestInterface $request, ResponseInterface $response) : self
    {
        $url = sprintf('%s?%s', $request->getUri()->getPath(), $request->getUri()->getQuery());
        $error = new static(sprintf(
            'The request to the URL "%s" resulted in a server error. The error response body was %s',
            $url,
            (string) $response->getBody()
        ));
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public function getRequest() :? RequestInterface
    {
        return $this->request;
    }

    public function getResponse() :? ResponseInterface
    {
        return $this->response;
    }
}
