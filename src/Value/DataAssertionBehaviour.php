<?php

declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Prismic\Exception\UnexpectedValue;

use function array_map;
use function assert;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function property_exists;

trait DataAssertionBehaviour
{
    private static function assertPropertyExists(object $object, string $property): void
    {
        if (! property_exists($object, $property)) {
            throw UnexpectedValue::withMissingProperty($object, $property);
        }
    }

    private static function assertObjectPropertyIsString(object $object, string $property): string
    {
        self::assertPropertyExists($object, $property);
        $value = $object->{$property};
        if (! is_string($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'string');
        }

        return $value;
    }

    private static function assertObjectPropertyIsInteger(object $object, string $property): int
    {
        self::assertPropertyExists($object, $property);
        $value = $object->{$property};
        if (! is_int($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'integer');
        }

        return $value;
    }

    private static function assertObjectPropertyIsIntegerish(object $object, string $property): int
    {
        self::assertPropertyExists($object, $property);
        if (! is_numeric($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'integer');
        }

        return (int) $object->{$property};
    }

    private static function assertObjectPropertyIsFloaty(object $object, string $property): float
    {
        self::assertPropertyExists($object, $property);
        if (! is_numeric($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'float');
        }

        return (float) $object->{$property};
    }

    private static function assertObjectPropertyIsBoolean(object $object, string $property): bool
    {
        self::assertPropertyExists($object, $property);
        $value = $object->{$property};
        if (! is_bool($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'boolean');
        }

        return $value;
    }

    /** @return mixed[] */
    private static function assertObjectPropertyIsArray(object $object, string $property): array
    {
        self::assertPropertyExists($object, $property);
        $value = $object->{$property};
        if (! is_array($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'array');
        }

        return $value;
    }

    private static function assertString(mixed $mixed): string
    {
        if (! is_string($mixed)) {
            throw new UnexpectedValue('Expected a string');
        }

        return $mixed;
    }

    /** @return array<array-key, string> */
    private static function assertObjectPropertyAllString(object $object, string $property): array
    {
        return array_map(static function ($value): string {
            return self::assertString($value);
        }, self::assertObjectPropertyIsArray($object, $property));
    }

    private static function assertObjectPropertyIsObject(object $object, string $property): object
    {
        self::assertPropertyExists($object, $property);
        $value = $object->{$property};
        if (! is_object($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'object');
        }

        return $value;
    }

    private static function optionalStringProperty(object $object, string $property): string|null
    {
        if (! property_exists($object, $property)) {
            return null;
        }

        $value = $object->{$property};
        if (! $value) {
            return null;
        }

        if (! is_string($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'string or null');
        }

        return $value;
    }

    /** @return mixed[]|null */
    private static function optionalArrayProperty(object $object, string $property): array|null
    {
        if (! property_exists($object, $property)) {
            return null;
        }

        return self::assertObjectPropertyIsArray($object, $property);
    }

    private static function optionalIntegerPropertyOrNull(object $object, string $property): int|null
    {
        if (! property_exists($object, $property)) {
            return null;
        }

        $value = $object->{$property};
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private static function assertObjectPropertyIsUtcDateTime(object $object, string $property): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat(
            DateTimeInterface::ATOM,
            self::assertObjectPropertyIsString($object, $property),
            new DateTimeZone('UTC'),
        );
        assert($date instanceof DateTimeImmutable);

        return $date;
    }
}
