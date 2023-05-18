<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Ref;
use PrismicTest\Framework\TestCase;

class RefTest extends TestCase
{
    public function testExpectedBehaviour(): void
    {
        $ref = Ref::new('foo', 'bar', 'baz', true);
        $this->assertSame('foo', $ref->id());
        $this->assertSame('bar', $ref->ref());
        $this->assertSame('baz', $ref->label());
        $this->assertTrue($ref->isMaster());
    }

    public function testRefCanBeCastToAString(): void
    {
        $ref = Ref::new('foo', 'bar', 'baz', true);
        $this->assertSame('bar', (string) $ref);
    }

    public function testThatTheRefCanBeNull(): void
    {
        $ref = Ref::new('foo', 'bar', null, false);
        $this->assertSame('bar', (string) $ref);
    }

    public function testFactory(): void
    {
        $input = (object) [
            'id' => 'foo',
            'ref' => 'ref',
            'label' => 'Master',
            'isMasterRef' => false,
        ];

        $ref = Ref::factory($input);

        self::assertSame('foo', $ref->id());
        self::assertSame('ref', $ref->ref());
        self::assertSame('Master', $ref->label());
        self::assertFalse($ref->isMaster());
    }

    public function testFactoryWithNullLabel(): void
    {
        $input = (object) [
            'id' => 'foo',
            'ref' => 'ref',
            'label' => null,
            'isMasterRef' => false,
        ];

        $ref = Ref::factory($input);

        self::assertSame('foo', $ref->id());
        self::assertSame('ref', $ref->ref());
        self::assertNull($ref->label());
        self::assertFalse($ref->isMaster());
    }

    public function testFactoryWithMissingIsMasterRefAndLabel(): void
    {
        $input = (object) [
            'id' => 'foo',
            'ref' => 'ref',
        ];

        $ref = Ref::factory($input);

        self::assertFalse($ref->isMaster());
    }
}
