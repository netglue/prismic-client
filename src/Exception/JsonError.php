<?php

declare(strict_types=1);

namespace Prismic\Exception;

use JsonException;

use function sprintf;

final class JsonError extends JsonException implements PrismicError
{
    /** @var string|null */
    private $payload;

    public static function unserializeFailed(JsonException $exception, string $payload): self
    {
        $error = new self(
            sprintf(
                'Failed to decode JSON payload: %s',
                $exception->getMessage()
            ),
            (int) $exception->getCode(),
            $exception
        );

        $error->payload = $payload;

        return $error;
    }

    public static function serializeFailed(JsonException $exception): self
    {
        return new self(
            sprintf(
                'Failed to encode the given data to a JSON string: %s',
                $exception->getMessage()
            ),
            (int) $exception->getCode(),
            $exception
        );
    }

    public static function cannotUnserializeToObject(string $payload): self
    {
        return new self(sprintf(
            'The given payload cannot be unserialized as an object: %s',
            $payload
        ));
    }

    public static function cannotUnserializeToArray(string $payload): self
    {
        return new self(sprintf(
            'The given payload cannot be unserialized as an array: %s',
            $payload
        ));
    }

    public function payload(): ?string
    {
        return $this->payload;
    }
}
