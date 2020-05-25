<?php
declare(strict_types=1);

namespace Prismic\Value;

use Prismic\Exception\UnexpectedValue;
use function is_array;
use function is_bool;
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
}
