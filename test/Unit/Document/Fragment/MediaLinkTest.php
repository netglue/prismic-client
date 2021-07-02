<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\MediaLink;
use PrismicTest\Framework\TestCase;

class MediaLinkTest extends TestCase
{
    public function testConstructor(): MediaLink
    {
        $link = MediaLink::new(
            'url',
            'filename',
            10
        );

        $this->expectNotToPerformAssertions();

        return $link;
    }

    /** @depends testConstructor */
    public function testUrlIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame('url', $link->url());
    }

    /** @depends testConstructor */
    public function testFilenameIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame('filename', $link->filename());
    }

    /** @depends testConstructor */
    public function testFileSizeIsExpectedValue(MediaLink $link): void
    {
        $this->assertSame(10, $link->filesize());
    }

    /** @depends testConstructor */
    public function testMediaLinksAreNotConsideredEmpty(MediaLink $link): void
    {
        $this->assertFalse($link->isEmpty());
    }
}
