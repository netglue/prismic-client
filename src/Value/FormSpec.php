<?php

declare(strict_types=1);

namespace Prismic\Value;

use ArrayIterator;
use IteratorAggregate;
use Prismic\Exception\UnknownFormField;
use Traversable;

use function array_keys;
use function array_map;
use function get_object_vars;

/** @implements IteratorAggregate<array-key, FormField> */
final class FormSpec implements IteratorAggregate
{
    use DataAssertionBehaviour;

    /** @var FormField[] */
    private array $fields;

    private function __construct(
        private string $id,
        private string|null $name,
        private string $method,
        private string|null $rel,
        private string $encType,
        private string $action,
        FormField ...$fields,
    ) {
        $this->fields = $fields;

        if ($this->hasField('routes')) {
            return;
        }

        $this->fields[] = FormField::new('routes', FormField::TYPE_STRING, false, null);
    }

    public static function factory(string $id, object $object): self
    {
        $fields = get_object_vars(self::assertObjectPropertyIsObject($object, 'fields'));

        return new self(
            $id,
            self::optionalStringProperty($object, 'name'),
            self::assertObjectPropertyIsString($object, 'method'),
            self::optionalStringProperty($object, 'rel'),
            self::assertObjectPropertyIsString($object, 'enctype'),
            self::assertObjectPropertyIsString($object, 'action'),
            ...array_map(static function (string $fieldName, object $spec): FormField {
                return FormField::factory($fieldName, $spec);
            }, array_keys($fields), $fields),
        );
    }

    public function name(): string|null
    {
        return $this->name;
    }

    public function rel(): string|null
    {
        return $this->rel;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function encType(): string
    {
        return $this->encType;
    }

    private function getField(string $name): FormField|null
    {
        foreach ($this->fields as $field) {
            if ($field->name() === $name) {
                return $field;
            }
        }

        return null;
    }

    public function hasField(string $name): bool
    {
        return $this->getField($name) instanceof FormField;
    }

    public function field(string $name): FormField
    {
        $field = $this->getField($name);
        if (! $field) {
            throw UnknownFormField::withOffendingKey($this, $name);
        }

        return $field;
    }

    /**
     * @return Traversable<array-key, FormField>
     * @psalm-return ArrayIterator<array-key, FormField>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->fields);
    }
}
