<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\Depends;
use Prismic\Document\Fragment\DocumentLink;
use PrismicTest\Framework\TestCase;
use TypeError;

class DocumentLinkTest extends TestCase
{
    public function testItIsATypeErrorForATagToBeANonString(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        DocumentLink::new(
            'id',
            'uid',
            'type',
            'en-gb',
            false,
            [1, 2],
        );
    }

    public function testConstructor(): DocumentLink
    {
        $link = DocumentLink::new(
            'id',
            'uid',
            'type',
            'en-gb',
            false,
            ['a', 'b'],
        );
        self::assertTrue(true);

        return $link;
    }

    /** @depends testConstructor */
    public function testThatCastingALinkToAStringWillYieldItsId(DocumentLink $link): void
    {
        self::assertSame('id', (string) $link);
    }

    public function testConstructorWithUrl(): DocumentLink
    {
        $link = DocumentLink::new(
            'id',
            'uid',
            'type',
            'en-gb',
            false,
            ['a', 'b'],
            '/some/url',
        );
        self::assertTrue(true);

        return $link;
    }

    #[Depends('testConstructor')]
    public function testThatIdIsExpectedValue(DocumentLink $link): void
    {
        $this->assertSame('id', $link->id());
    }

    #[Depends('testConstructor')]
    public function testThatUidIsExpectedValue(DocumentLink $link): void
    {
        $this->assertSame('uid', $link->uid());
    }

    #[Depends('testConstructor')]
    public function testThatTypeIsExpectedValue(DocumentLink $link): void
    {
        $this->assertSame('type', $link->type());
    }

    #[Depends('testConstructor')]
    public function testThatLanguageIsExpectedValue(DocumentLink $link): void
    {
        $this->assertSame('en-gb', $link->language());
    }

    #[Depends('testConstructor')]
    public function testThatIsBrokenIsExpectedValue(DocumentLink $link): void
    {
        $this->assertFalse($link->isBroken());
    }

    #[Depends('testConstructor')]
    public function testThatALinkIsNotConsideredEmpty(DocumentLink $link): void
    {
        $this->assertFalse($link->isEmpty());
    }

    #[Depends('testConstructor')]
    public function testThatTagsHaveExpectedMembers(DocumentLink $link): void
    {
        $this->assertContainsEquals('a', $link->tags());
        $this->assertContainsEquals('b', $link->tags());
    }

    /** @depends testConstructor */
    public function testTheLinkMayHaveAUrlAndItIsNullByDefault(DocumentLink $link): void
    {
        self::assertNull($link->url());
    }

    /** @depends testConstructorWithUrl */
    public function testThatUrlReturnsTheExpectedValue(DocumentLink $link): void
    {
        self::assertSame('/some/url', $link->url());
    }
}
