<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Depends;
use Prismic\Document\Fragment\DocumentLink;
use PrismicTest\Framework\TestCase;
use TypeError;

final class DocumentLinkTest extends TestCase
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

    public function testExtendedInformation(): DocumentLink
    {
        $firstPublicationDate = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-01-01', new DateTimeZone('UTC'));
        $lastPublicationDate = DateTimeImmutable::createFromFormat('!Y-m-d', '2020-02-02', new DateTimeZone('UTC'));

        self::assertNotFalse($firstPublicationDate);
        self::assertNotFalse($lastPublicationDate);

        return DocumentLink::withExtendedInformation(
            'some-id',
            'some-uid',
            'some-type',
            'en-gb',
            false,
            ['foo', 'bar'],
            'some-slug',
            $firstPublicationDate,
            $lastPublicationDate,
        );
    }

    #[Depends('testConstructor')]
    #[Depends('testExtendedInformation')]
    public function testThatLinksCanReportFirstPublicationDate(DocumentLink $link, DocumentLink $extended): void
    {
        self::assertNull($link->firstPublished());

        $date = $extended->firstPublished();
        self::assertNotNull($date);
        self::assertSame('2020-01-01', $date->format('Y-m-d'));
    }

    #[Depends('testConstructor')]
    #[Depends('testExtendedInformation')]
    public function testThatLinksCanReportLastPublicationDate(DocumentLink $link, DocumentLink $extended): void
    {
        self::assertNull($link->lastPublished());

        $date = $extended->lastPublished();
        self::assertNotNull($date);
        self::assertSame('2020-02-02', $date->format('Y-m-d'));
    }

    #[Depends('testConstructor')]
    #[Depends('testExtendedInformation')]
    public function testThatLinksCanBeAssociatedWithASlug(DocumentLink $link, DocumentLink $extended): void
    {
        self::assertNull($link->slug());
        self::assertSame('some-slug', $extended->slug());
    }
}
