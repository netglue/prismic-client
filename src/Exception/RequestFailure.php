<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function sprintf;

class RequestFailure extends RuntimeException implements PrismicError
{
    protected RequestInterface|null $request = null;
    protected ResponseInterface|null $response = null;

    public static function withClientException(ClientExceptionInterface $exception): self
    {
        return new self(
            $exception->getMessage(),
            $exception->getCode(),
            $exception,
        );
    }

    public static function withRedirectResponse(RequestInterface $request, ResponseInterface $response): self
    {
        $error = new self(sprintf(
            'The request to the URL "%s" resulted in a %d redirect. I don’t know what to do with that.',
            (string) $request->getUri(),
            $response->getStatusCode(),
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public static function withClientError(RequestInterface $request, ResponseInterface $response): self
    {
        $status = $response->getStatusCode();
        if ($status === 401 || $status === 403) {
            return AuthenticationError::with($request, $response);
        }

        if (PreviewTokenExpired::isPreviewTokenExpiry($response)) {
            return PreviewTokenExpired::with($request, $response);
        }

        $error = new self(sprintf(
            'Error %d. The request to the URL "%s" was rejected by the api. The error response body was "%s"',
            $status,
            (string) $request->getUri(),
            (string) $response->getBody(),
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public static function withServerError(RequestInterface $request, ResponseInterface $response): self
    {
        $error = new self(sprintf(
            'The request to the URL "%s" resulted in a server error. The error response body was "%s"',
            (string) $request->getUri(),
            (string) $response->getBody(),
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    public function getRequest(): RequestInterface|null
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface|null
    {
        return $this->response;
    }
}
