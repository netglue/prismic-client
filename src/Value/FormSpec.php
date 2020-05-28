<?php
declare(strict_types=1);

namespace Prismic\Value;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Prismic\Exception\UnknownFormField;
use Traversable;
use function array_keys;
use function array_map;
use function get_object_vars;

final class FormSpec implements IteratorAggregate
{
    use DataAssertionBehaviour;

    /** @var string */
    private $id;
    /** @var string|null */
    private $name;
    /** @var string */
    private $method;
    /** @var string|null */
    private $rel;
    /** @var string */
    private $encType;
    /** @var string */
    private $action;
    /** @var FormField[] */
    private $fields;

    private function __construct(
        string $id,
        ?string $name,
        string $method,
        ?string $rel,
        string $encType,
        string $action,
        FormField ...$fields
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->method = $method;
        $this->rel = $rel;
        $this->encType = $encType;
        $this->action = $action;
        $this->fields = $fields;
    }

    public static function factory(string $id, object $object) : self
    {
        $fields = get_object_vars(self::assertObjectPropertyIsObject($object, 'fields'));

        return new static(
            $id,
            self::optionalStringProperty($object, 'name'),
            self::assertObjectPropertyIsString($object, 'method'),
            self::optionalStringProperty($object, 'rel'),
            self::assertObjectPropertyIsString($object, 'enctype'),
            self::assertObjectPropertyIsString($object, 'action'),
            ...array_map(static function (string $fieldName, object $spec) : FormField {
                return FormField::factory($fieldName, $spec);
            }, array_keys($fields), $fields)
        );
    }

    public function id() : string
    {
        return $this->id;
    }

    public function action() : string
    {
        return $this->action;
    }

    public function method() : string
    {
        return $this->method;
    }

    public function encType() : string
    {
        return $this->encType;
    }

    private function getField(string $name) :? FormField
    {
        foreach ($this->fields as $field) {
            if ($field->name() === $name) {
                return $field;
            }
        }

        return null;
    }

    public function hasField(string $name) : bool
    {
        return $this->getField($name) instanceof FormField;
    }

    public function field(string $name) : FormField
    {
        $field = $this->getField($name);
        if (! $field) {
            throw UnknownFormField::withOffendingKey($this, $name);
        }

        return $field;
    }

    /** @return FormField[] */
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->fields);
    }
}
