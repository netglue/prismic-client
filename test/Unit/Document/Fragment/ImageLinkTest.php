<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\ImageLink;
use PrismicTest\Framework\TestCase;

class ImageLinkTest extends TestCase
{
    public function testConstructor(): ImageLink
    {
        $link = ImageLink::new(
            'url',
            'filename',
            10,
            20,
            30,
        );

        $this->expectNotToPerformAssertions();

        return $link;
    }

    /** @depends testConstructor */
    public function testUrlIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame('url', $link->url());
    }

    /** @depends testConstructor */
    public function testFilenameIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame('filename', $link->filename());
    }

    /** @depends testConstructor */
    public function testFileSizeIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(10, $link->filesize());
    }

    /** @depends testConstructor */
    public function testWidthIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(20, $link->width());
    }

    /** @depends testConstructor */
    public function testHeightIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(30, $link->height());
    }

    /** @depends testConstructor */
    public function testImageLinksAreNotConsideredEmpty(ImageLink $link): void
    {
        $this->assertFalse($link->isEmpty());
    }
}
