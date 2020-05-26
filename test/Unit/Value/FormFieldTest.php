<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\FormField;
use PrismicTest\Framework\TestCase;

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
}
