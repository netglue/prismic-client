<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Prismic\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function array_intersect;
use function array_map;
use function count;
use function explode;
use function is_string;
use function property_exists;
use function sprintf;
use function str_contains;

final class PreviewTokenExpired extends RequestFailure
{
    private const array MAGIC_WORDS = [
        'preview',
        'token',
        'expired',
    ];

    public static function isPreviewTokenExpiry(ResponseInterface $response): bool
    {
        $type = $response->getHeaderLine('content-type');
        if (! str_contains($type, 'json')) {
            return false;
        }

        try {
            $payload = Json::decodeObject((string) $response->getBody());
        } catch (JsonError) {
            return false;
        }

        $error = self::extractErrorMessage($payload);

        if ($error === null) {
            return false;
        }

        return self::errorStringHasMagicWords($error);
    }

    public static function with(RequestInterface $request, ResponseInterface $response): self
    {
        $error = new self(sprintf(
            'Error %d. The preview token provided has expired',
            $response->getStatusCode(),
        ), $response->getStatusCode());
        $error->request = $request;
        $error->response = $response;

        return $error;
    }

    private static function extractErrorMessage(object $payload): string|null
    {
        foreach (['error', 'message'] as $key) {
            if (! property_exists($payload, $key)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $value = $payload->{$key};
            if (! is_string($value)) {
                continue;
            }

            return $value;
        }

        return null;
    }

    private static function errorStringHasMagicWords(string $error): bool
    {
        // We seem to get 404 errors these days when a preview is no longer accessible…
        if (str_contains($error, 'websitePreviewId=') && str_contains($error, 'not found')) {
            return true;
        }

        $words = array_map('strtolower', explode(' ', $error));

        return count(array_intersect($words, self::MAGIC_WORDS)) === count(self::MAGIC_WORDS);
    }
}
