<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Exception\InvalidArgument;
use Prismic\Exception\UnexpectedValue;
use Prismic\Json;
use Prismic\Value\FormField;
use PrismicTest\Framework\TestCase;
use stdClass;

class FormFieldTest extends TestCase
{
    public function testBasicBehaviour(): void
    {
        $field = FormField::new('foo', 'bar', true, 'baz');
        $this->assertEquals('foo', $field->name());
        $this->assertEquals('bar', $field->type());
        $this->assertTrue($field->isMultiple());
        $this->assertEquals('baz', $field->defaultValue());
    }

    public function testDefaultValueCanBeNull(): void
    {
        $field = FormField::new('foo', 'bar', true, null);
        $this->assertNull($field->defaultValue());
    }

    public function testFactoryExceptionForMissingType(): void
    {
        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('Expected an object to contain the property "type"');
        FormField::factory('foo', new stdClass());
    }

    public function testFactoryExceptionForNonStringType(): void
    {
        $this->expectException(UnexpectedValue::class);
        $object = new stdClass();
        $object->type = 1;
        $this->expectExceptionMessage('Expected the object property "type" to be a string');
        FormField::factory('foo', $object);
    }

    public function testNonBooleanForMultipleIsExceptional(): void
    {
        $object = Json::decodeObject('{
            "type":"t",
            "multiple":"m",
            "default":"foo"
        }');
        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('Expected the object property "multiple" to be a boolean');
        FormField::factory('foo', $object);
    }

    public function testDefaultValueMustBeString(): void
    {
        $object = Json::decodeObject('{
            "type":"t",
            "multiple":false,
            "default":1
        }');
        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('Expected the object property "default" to be a string or null');
        FormField::factory('foo', $object);
    }

    public function testTypeExpectations(): void
    {
        $field = FormField::factory('foo', Json::decodeObject('{
            "type":"t",
            "multiple":false,
            "default":"baz"
        }'));
        $this->assertFalse($field->expectsInteger());
        $this->assertFalse($field->expectsString());

        $field = FormField::factory('foo', Json::decodeObject('{
            "type":"String",
            "multiple":false,
            "default":"baz"
        }'));

        $this->assertFalse($field->expectsInteger());
        $this->assertTrue($field->expectsString());

        $field = FormField::factory('foo', Json::decodeObject('{
            "type":"Integer",
            "multiple":false,
            "default":"baz"
        }'));

        $this->assertTrue($field->expectsInteger());
        $this->assertFalse($field->expectsString());
    }

    /** @return mixed[] */
    public function invalidNumbers(): iterable
    {
        return [
            ['foo'],
            [['a','b']],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider invalidNumbers
     */
    public function testInvalidIntegerValues($value): void
    {
        $field = FormField::factory('foo', Json::decodeObject('{
            "type":"Integer",
            "multiple":false,
            "default":"1"
        }'));

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('The form field "foo" expects an integer value but received');
        $field->validateValue($value);
    }

    /** @return mixed[] */
    public function invalidStrings(): iterable
    {
        return [
            [true],
            [1],
            [0.5],
            [['foo']],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider invalidStrings
     */
    public function testInvalidStringValues($value): void
    {
        $field = FormField::factory('foo', Json::decodeObject('{
            "type":"String",
            "multiple":false,
            "default":"1"
        }'));

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('The form field "foo" expects a string value but received');
        $field->validateValue($value);
    }
}
