<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\WebLink;
use PrismicTest\Framework\TestCase;

class WebLinkTest extends TestCase
{
    public function testAccessors(): void
    {
        $link = WebLink::new('somewhere', 'target');
        $this->assertSame('somewhere', $link->url());
        $this->assertSame('target', $link->target());
    }

    public function testThatCastingToStringYieldsTheUrl(): void
    {
        $link = WebLink::new('somewhere', 'target');
        $this->assertSame('somewhere', (string) $link);
    }

    public function testThatWebLinksAreNotConsideredEmpty(): void
    {
        $link = WebLink::new('somewhere', 'target');
        $this->assertFalse($link->isEmpty());
    }
}
