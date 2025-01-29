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
        $this->expectNotToPerformAssertions();

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
}
