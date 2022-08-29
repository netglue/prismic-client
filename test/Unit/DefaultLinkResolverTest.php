<?php

declare(strict_types=1);

namespace PrismicTest;

use PHPUnit\Framework\TestCase;
use Prismic\DefaultLinkResolver;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\MediaLink;
use Prismic\Link;

class DefaultLinkResolverTest extends TestCase
{
    /** @var DefaultLinkResolver */
    private $linkResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->linkResolver = new class extends DefaultLinkResolver {
            protected function resolveDocumentLink(DocumentLink $link): ?string
            {
                return '/some/url';
            }
        };
    }

    public function testMediaUrlsReturnTheExpectedUrl(): void
    {
        $media = MediaLink::new('/foo', 'whatever.jpg', 1234);

        self::assertSame('/foo', $this->linkResolver->resolve($media));
    }

    public function testDocLinksWillYieldStoredUrlWhenPresent(): void
    {
        $docLink = DocumentLink::new('id', 'uid', 'foo', 'en-gb', false, [], '/special');

        self::assertSame('/special', $this->linkResolver->resolve($docLink));
    }

    public function testDocLinksWillUseCustomResolveWhenNull(): void
    {
        $docLink = DocumentLink::new('id', 'uid', 'foo', 'en-gb', false, [], null);

        self::assertSame('/some/url', $this->linkResolver->resolve($docLink));
    }

    public function testUnknownLinkTypesWillYieldNull(): void
    {
        $link = $this->createMock(Link::class);

        self::assertNull($this->linkResolver->resolve($link));
    }
}
