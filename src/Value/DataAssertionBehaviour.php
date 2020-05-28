<?php
declare(strict_types=1);

namespace Prismic\Value;

use DateTimeImmutable;
use DateTimeZone;
use Prismic\Exception\UnexpectedValue;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function property_exists;

trait DataAssertionBehaviour
{
    private static function assertPropertyExists(object $object, string $property) : void
    {
        if (! property_exists($object, $property)) {
            throw UnexpectedValue::withMissingProperty($object, $property);
        }
    }

    private static function assertObjectPropertyIsString(object $object, string $property) : string
    {
        self::assertPropertyExists($object, $property);
        if (! is_string($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'string');
        }

        return $object->{$property};
    }

    private static function assertObjectPropertyIsInteger(object $object, string $property) : int
    {
        self::assertPropertyExists($object, $property);
        if (! is_int($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'integer');
        }

        return $object->{$property};
    }

    private static function assertObjectPropertyIsIntegerish(object $object, string $property) : int
    {
        self::assertPropertyExists($object, $property);
        if (! is_numeric($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'integer');
        }

        return (int) $object->{$property};
    }

    private static function assertObjectPropertyIsFloat(object $object, string $property) : float
    {
        self::assertPropertyExists($object, $property);
        if (! is_float($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'float');
        }

        return $object->{$property};
    }

    private static function assertObjectPropertyIsBoolean(object $object, string $property) : bool
    {
        self::assertPropertyExists($object, $property);
        if (! is_bool($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'boolean');
        }

        return $object->{$property};
    }

    /** @return mixed[] */
    private static function assertObjectPropertyIsArray(object $object, string $property) : array
    {
        self::assertPropertyExists($object, $property);
        if (! is_array($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'array');
        }

        return $object->{$property};
    }

    private static function assertObjectPropertyIsObject(object $object, string $property) : object
    {
        self::assertPropertyExists($object, $property);
        if (! is_object($object->{$property})) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'object');
        }

        return $object->{$property};
    }

    private static function optionalStringProperty(object $object, string $property) :? string
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

    private static function optionalIntegerProperty(object $object, string $property) :? int
    {
        if (! property_exists($object, $property) || $object->{$property} === null) {
            return null;
        }

        $value = $object->{$property};
        if (! is_numeric($value)) {
            throw UnexpectedValue::withInvalidPropertyType($object, $property, 'number');
        }

        return (int) $value;
    }

    private static function assertObjectPropertyIsUtcDateTime(object $object, string $property) : DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat(
            DateTimeImmutable::ATOM,
            self::assertObjectPropertyIsString($object, $property),
            new DateTimeZone('UTC')
        );
    }
}
