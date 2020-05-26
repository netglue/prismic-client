<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Ref;
use PrismicTest\Framework\TestCase;

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

    public function testRefCanBeCastToAString() : void
    {
        $ref = Ref::new('foo', 'bar', 'baz', true);
        $this->assertSame('bar', (string) $ref);
    }
}
