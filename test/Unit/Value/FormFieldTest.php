<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Exception\UnexpectedValue;
use Prismic\Json;
use Prismic\Value\FormField;
use PrismicTest\Framework\TestCase;
use stdClass;

class FormFieldTest extends TestCase
{
    public function testBasicBehaviour() : void
    {
        $field = FormField::new('foo', 'bar', true, 'baz');
        $this->assertEquals('foo', $field->name());
        $this->assertEquals('bar', $field->type());
        $this->assertTrue($field->isMultiple());
        $this->assertEquals('baz', $field->defaultValue());
    }

    public function testDefaultValueCanBeNull() : void
    {
        $field = FormField::new('foo', 'bar', true, null);
        $this->assertNull($field->defaultValue());
    }

    public function testFactoryExceptionForMissingType() : void
    {
        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('Expected an object to contain the property "type"');
        FormField::factory('foo', new stdClass());
    }

    public function testFactoryExceptionForNonStringType() : void
    {
        $this->expectException(UnexpectedValue::class);
        $object = new stdClass();
        $object->type = 1;
        $this->expectExceptionMessage('Expected the object property "type" to be a string');
        FormField::factory('foo', $object);
    }

    public function testNonBooleanForMultipleIsExceptional() : void
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

    public function testDefaultValueMustBeString() : void
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
}
