<?php
declare(strict_types=1);

namespace Prismic\Exception;

use Prismic\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function sprintf;
use function strpos;

final class PreviewTokenExpired extends RequestFailure
{
    public static function isPreviewTokenExpiry(ResponseInterface $response) : bool
    {
        $type = $response->getHeaderLine('content-type');
        if (strpos($type, 'json') === false) {
            return false;
        }

        $payload = Json::decodeObject((string) $response->getBody());
        $error = $payload->error ?? null;

        return $error === 'Preview token expired';
    }

    public static function with(RequestInterface $request, ResponseInterface $response) : self
    {
        $status = $response->getStatusCode();
        $error = new static(sprintf(
            'Error %d. The preview token provided has expired',
            $status
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }
}
