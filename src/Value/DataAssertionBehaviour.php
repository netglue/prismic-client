<?php

declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Prismic\Exception\UnexpectedValue;

use function assert;
use function gettype;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function property_exists;
use function sprintf;

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
        if (! is_string($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'string');
        }

        return $object->{$property};
    }

    private static function assertObjectPropertyIsInteger(object $object, string $property): int
    {
        self::assertPropertyExists($object, $property);
        if (! is_int($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'integer');
        }

        return $object->{$property};
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
        if (! is_bool($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'boolean');
        }

        return $object->{$property};
    }

    /** @return mixed[] */
    private static function assertObjectPropertyIsArray(object $object, string $property): array
    {
        self::assertPropertyExists($object, $property);
        if (! is_array($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'array');
        }

        return $object->{$property};
    }

    /** @return array<array-key, string> */
    private static function assertObjectPropertyAllString(object $object, string $property): array
    {
        $strings = self::assertObjectPropertyIsArray($object, $property);

        foreach ($strings as $string) {
            if (is_string($string)) {
                continue;
            }

            throw new UnexpectedValue(sprintf(
                'Expected the array in the property "%s" to contain only strings, but found an element of type "%s"',
                $property,
                gettype($string)
            ));
        }

        return $strings;
    }

    private static function assertObjectPropertyIsObject(object $object, string $property): object
    {
        self::assertPropertyExists($object, $property);
        if (! is_object($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'object');
        }

        return $object->{$property};
    }

    private static function optionalStringProperty(object $object, string $property): ?string
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
    private static function optionalArrayProperty(object $object, string $property): ?array
    {
        if (! property_exists($object, $property) || ! $object->{$property}) {
            return null;
        }

        return self::assertObjectPropertyIsArray($object, $property);
    }

    private static function optionalIntegerPropertyOrNull(object $object, string $property): ?int
    {
        if (! property_exists($object, $property) || $object->{$property} === null) {
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
            new DateTimeZone('UTC')
        );
        assert($date instanceof DateTimeImmutable);

        return $date;
    }
}
