<?php

declare(strict_types=1);

namespace Prismic\Exception;

use InvalidArgumentException;
use Prismic\Json;
use Prismic\Value\FormField;

use function gettype;
use function is_object;
use function sprintf;

class InvalidArgument extends InvalidArgumentException implements PrismicError
{
    public static function scalarExpected(mixed $received): self
    {
        return new self(sprintf(
            'A scalar argument was expected but %s was received',
            is_object($received) ? $received::class : gettype($received),
        ));
    }

    public static function numberExpected(mixed $received): self
    {
        return new self(sprintf(
            'Either a float or an integer was expected but %s was received',
            is_object($received) ? $received::class : gettype($received),
        ));
    }

    public static function invalidColor(string $value): self
    {
        return new self(sprintf(
            'Expected a string that looks like a hex colour with a # prefix but received "%s"',
            $value,
        ));
    }

    public static function invalidDateFormat(string $expectedFormat, string $value): self
    {
        return new self(sprintf(
            'Expected a date value in the format %s but received "%s"',
            $expectedFormat,
            $value,
        ));
    }

    public static function unknownLinkType(string $type, object $payload): self
    {
        return new self(sprintf(
            'The link type "%s" is not a known type of link. Found in the object: %s',
            $type,
            Json::encode($payload),
        ));
    }

    public static function fieldExpectsString(FormField $field, mixed $invalidValue): self
    {
        return new self(sprintf(
            'The form field "%s" expects a string value but received %s',
            $field->name(),
            is_object($invalidValue) ? $invalidValue::class : gettype($invalidValue),
        ));
    }

    public static function fieldExpectsNumber(FormField $field, mixed $invalidValue): self
    {
        return new self(sprintf(
            'The form field "%s" expects an integer value but received %s',
            $field->name(),
            is_object($invalidValue) ? $invalidValue::class : gettype($invalidValue),
        ));
    }
}
