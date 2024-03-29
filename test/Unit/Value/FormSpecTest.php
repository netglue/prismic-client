<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Exception\UnknownFormField;
use Prismic\Json;
use Prismic\Value\FormSpec;
use PrismicTest\Framework\TestCase;

class FormSpecTest extends TestCase
{
    private FormSpec $form;

    protected function setUp(): void
    {
        parent::setUp();

        $json = <<<'EOF'
        {
            "name" : "My Form",
            "rel" : "something",
            "method": "GET",
            "action": "https://example.com",
            "enctype": "whatever",
            "fields": {
                "my-field": {
                    "type":"Integer",
                    "multiple":false,
                    "default":"10"
                }
            }
        }
        EOF;
        $this->form = FormSpec::factory('my-form', Json::decodeObject($json));
    }

    public function testBasicAccessors(): void
    {
        $this->assertEquals('My Form', $this->form->name());
        $this->assertEquals('something', $this->form->rel());
        $this->assertEquals('GET', $this->form->method());
        $this->assertEquals('https://example.com', $this->form->action());
        $this->assertEquals('whatever', $this->form->encType());
        $this->assertEquals('my-form', $this->form->id());
    }

    public function testHasField(): void
    {
        $this->assertFalse($this->form->hasField('unknown'));
        $this->assertTrue($this->form->hasField('my-field'));
    }

    public function testFormFieldsCanBeRetrieved(): void
    {
        $field = $this->form->field('my-field');
        $this->assertSame('Integer', $field->type());
    }

    public function testItsExceptionalToFetchAnUnknownFormField(): void
    {
        $this->expectException(UnknownFormField::class);
        $this->form->field('unknown');
    }
}
