<?php

declare(strict_types=1);

namespace Prismic;

use JsonException;
use Prismic\Exception\JsonError;

use function is_array;
use function is_object;
use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class Json
{
    /** @throws JsonError If decoding the payload fails for any reason. */
    public static function decodeObject(string $jsonString): object
    {
        try {
            $object = json_decode($jsonString, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        }

        if (! is_object($object)) {
            throw JsonError::cannotUnserializeToObject($jsonString);
        }

        return $object;
    }

    /** @return array<array-key, mixed> */
    public static function decodeArray(string $jsonString): array
    {
        try {
            $array = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        }

        if (! is_array($array)) {
            throw JsonError::cannotUnserializeToArray($jsonString);
        }

        return $array;
    }

    /**
     * Decode a json string without enforcing the return type
     *
     * @throws JsonError If decoding the payload fails for any reason.
     */
    public static function decode(string $jsonString, bool $asArray): mixed
    {
        try {
            return json_decode($jsonString, $asArray, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        }
    }

    /** @throws JsonError If encoding the value fails for any reason. */
    public static function encode(mixed $value, int $flags = 0): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR | $flags);
        } catch (JsonException $exception) {
            throw JsonError::serializeFailed($exception);
        }
    }
}
