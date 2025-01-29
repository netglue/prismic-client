<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\Depends;
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

    #[Depends('testConstructor')]
    public function testUrlIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame('url', $link->url());
    }

    #[Depends('testConstructor')]
    public function testFilenameIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame('filename', $link->filename());
    }

    #[Depends('testConstructor')]
    public function testFileSizeIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(10, $link->filesize());
    }

    #[Depends('testConstructor')]
    public function testWidthIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(20, $link->width());
    }

    #[Depends('testConstructor')]
    public function testHeightIsExpectedValue(ImageLink $link): void
    {
        $this->assertSame(30, $link->height());
    }

    #[Depends('testConstructor')]
    public function testImageLinksAreNotConsideredEmpty(ImageLink $link): void
    {
        $this->assertFalse($link->isEmpty());
    }
}
