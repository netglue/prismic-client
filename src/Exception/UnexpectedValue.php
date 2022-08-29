<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Prismic\Json;
use UnexpectedValueException;

use function gettype;
use function sprintf;

final class UnexpectedValue extends UnexpectedValueException implements PrismicError
{
    public static function withMissingProperty(object $object, string $property): self
    {
        $message = sprintf(
            'Expected an object to contain the property "%s" but it was not present: Received %s',
            $property,
            Json::encode($object),
        );

        return new self($message);
    }

    public static function withInvalidPropertyType(object $object, string $property, string $expectedType): self
    {
        return new self(sprintf(
            'Expected the object property "%s" to be a %s but received %s. Object: %s',
            $property,
            $expectedType,
            gettype($object->{$property}),
            Json::encode($object),
        ));
    }

    public static function missingMasterRef(): self
    {
        return new self('No master ref can be determined');
    }
}
