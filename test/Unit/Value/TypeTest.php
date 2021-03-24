<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Type;
use PrismicTest\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

class TypeTest extends TestCase
{
    public function testNewInstance(): void
    {
        $type = Type::new('foo', 'bar');
        self::assertEquals('foo', $type->id());
        self::assertEquals('bar', $type->name());
    }

    public function testJsonEncode(): void
    {
        self::assertEquals(
            '{"foo":"bar"}',
            json_encode(Type::new('foo', 'bar'), JSON_THROW_ON_ERROR)
        );
    }

    public function testThatTheStringValueIsTheTypeIdentifier(): void
    {
        $type = Type::new('foo', 'bar');
        self::assertEquals('foo', (string) $type);
    }
}
