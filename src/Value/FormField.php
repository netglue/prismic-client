<?php
declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;

final class FormField implements JsonSerializable
{
    use DataAssertionBehaviour;

    /** @var string */
    private $name;
    /** @var string */
    private $type;
    /** @var bool */
    private $multiple;
    /** @var string|null */
    private $default;

    private function __construct(string $name, string $type, bool $multiple, ?string $default)
    {
        $this->name = $name;
        $this->type = $type;
        $this->multiple = $multiple;
        $this->default = $default;
    }

    public static function new(string $name, string $type, bool $multiple, ?string $default) : self
    {
        return new static($name, $type, $multiple, $default);
    }

    public static function factory(string $name, object $value) : self
    {
        return new static(
            $name,
            self::assertObjectPropertyIsString($value, 'type'),
            self::assertObjectPropertyIsBoolean($value, 'multiple'),
            self::optionalStringProperty($value, 'default')
        );
    }

    public function name() : string
    {
        return $this->name;
    }

    public function type() : string
    {
        return $this->type;
    }

    public function isMultiple() : bool
    {
        return $this->multiple;
    }

    public function defaultValue() :? string
    {
        return $this->default;
    }

    /** @return mixed[] */
    public function jsonSerialize() : array
    {
        return [
            'type' => $this->type,
            'multiple' => $this->multiple,
            'default' => $this->default,
        ];
    }
}
