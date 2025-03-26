<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\Depends;
use Prismic\Document\Fragment\MediaLink;
use PrismicTest\Framework\TestCase;

final class MediaLinkTest extends TestCase
{
    public function testConstructor(): MediaLink
    {
        $link = MediaLink::new(
            'url',
            'filename',
            10,
        );

        $this->expectNotToPerformAssertions();

        return $link;
    }

    #[Depends('testConstructor')]
    public function testUrlIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame('url', $link->url());
    }

    #[Depends('testConstructor')]
    public function testFilenameIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame('filename', $link->filename());
    }

    #[Depends('testConstructor')]
    public function testFileSizeIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame(10, $link->filesize());
    }

    #[Depends('testConstructor')]
    public function testMediaLinksAreNotConsideredEmpty(MediaLink $link): void
    {
        $this->assertFalse($link->isEmpty());
    }
}
