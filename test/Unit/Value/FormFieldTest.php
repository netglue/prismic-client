<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\FormField;
use PrismicTest\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

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

    public function testJsonEncode() : void
    {
        $this->assertJsonStringEqualsJsonString(
            '{"type":"String","multiple":true,"default":"baz"}',
            json_encode(FormField::new('my-field', 'String', true, 'baz'), JSON_THROW_ON_ERROR)
        );
    }
}
