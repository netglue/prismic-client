<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\TestCase;
use Prismic\Document\Fragment\TextLink;
use Prismic\Document\Fragment\WebLink;

final class TextLinkTest extends TestCase
{
    public function testTextLinksAreNotConsideredEmpty(): void
    {
        $link = TextLink::new(
            'Foo',
            WebLink::new('foo', null),
        );

        self::assertFalse($link->isEmpty());
    }

    public function testStringRepresentationIsSameAsTheUnderlyingLink(): void
    {
        $link = TextLink::new(
            'Foo',
            WebLink::new('foo', null),
        );

        self::assertSame($link->link->__toString(), $link->__toString());
    }
}
