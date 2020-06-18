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
    public const EXPECTED_ERROR_MESSAGE = 'Preview token expired';

    public static function isPreviewTokenExpiry(ResponseInterface $response) : bool
    {
        $type = $response->getHeaderLine('content-type');
        if (strpos($type, 'json') === false) {
            return false;
        }

        $payload = Json::decodeObject((string) $response->getBody());
        $error = $payload->error ?? null;

        return $error === self::EXPECTED_ERROR_MESSAGE;
    }

    public static function with(RequestInterface $request, ResponseInterface $response) : self
    {
        $error = new static(sprintf(
            'Error %d. The preview token provided has expired',
            $response->getStatusCode()
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }
}
