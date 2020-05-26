<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Bookmark;
use PrismicTest\Framework\TestCase;

class BookmarkTest extends TestCase
{
    public function testNewInstance() : void
    {
        $bookmark = Bookmark::new('foo', 'bar');
        $this->assertEquals('foo', $bookmark->name());
        $this->assertEquals('bar', $bookmark->documentId());
    }
}
