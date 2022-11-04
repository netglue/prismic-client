<?php

declare(strict_types=1);

namespace Prismic\Value;

use Prismic\Exception\InvalidArgument;

use function is_numeric;
use function is_string;

final class FormField
{
    use DataAssertionBehaviour;

    public const TYPE_STRING = 'String';
    public const TYPE_INTEGER = 'Integer';

    private function __construct(
        private string $name,
        private string $type,
        private bool $multiple,
        private string|null $default,
    ) {
    }

    public static function new(string $name, string $type, bool $multiple, string|null $default): self
    {
        return new self($name, $type, $multiple, $default);
    }

    public static function factory(string $name, object $value): self
    {
        return new self(
            $name,
            self::assertObjectPropertyIsString($value, 'type'),
            self::assertObjectPropertyIsBoolean($value, 'multiple'),
            self::optionalStringProperty($value, 'default'),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function defaultValue(): string|null
    {
        return $this->default;
    }

    public function expectsString(): bool
    {
        return $this->type === self::TYPE_STRING;
    }

    public function expectsInteger(): bool
    {
        return $this->type === self::TYPE_INTEGER;
    }

    public function validateValue(mixed $value): void
    {
        if (! is_string($value) && $this->expectsString()) {
            throw InvalidArgument::fieldExpectsString($this, $value);
        }

        if (! is_numeric($value) && $this->expectsInteger()) {
            throw InvalidArgument::fieldExpectsNumber($this, $value);
        }
    }
}
