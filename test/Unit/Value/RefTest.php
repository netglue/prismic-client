<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Ref;
use PrismicTest\Framework\TestCase;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class RefTest extends TestCase
{
    public function testExpectedBehaviour() : void
    {
        $ref = Ref::new('foo', 'bar', 'baz', true);
        $this->assertSame('foo', $ref->id());
        $this->assertSame('bar', $ref->ref());
        $this->assertSame('baz', $ref->label());
        $this->assertTrue($ref->isMaster());
    }

    public function testRefCanBeSerializedToJson() : void
    {
        $value = json_encode(Ref::new('a', 'b', 'c', true), JSON_THROW_ON_ERROR);
        $this->assertEquals('{"id":"a","ref":"b","label":"c","isMasterRef":true}', $value);
    }

    public function testRefCanBeCastToAString() : void
    {
        $ref = Ref::new('foo', 'bar', 'baz', true);
        $this->assertSame('bar', (string) $ref);
    }
}
