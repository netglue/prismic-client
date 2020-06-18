<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\DocumentLink;
use PrismicTest\Framework\TestCase;
use TypeError;

class DocumentLinkTest extends TestCase
{
    public function testItIsATypeErrorForATagToBeANonString() : void
    {
        $this->expectException(TypeError::class);
        DocumentLink::new(
            'id',
            'uid',
            'type',
            'en-gb',
            false,
            [1, 2],
        );
    }

    public function testConstructor() : DocumentLink
    {
        $link = DocumentLink::new(
            'id',
            'uid',
            'type',
            'en-gb',
            false,
            ['a', 'b'],
        );
        $this->addToAssertionCount(1);

        return $link;
    }

    /** @depends testConstructor */
    public function testThatIdIsExpectedValue(DocumentLink $link) : void
    {
        $this->assertSame('id', $link->id());
    }

    /** @depends testConstructor */
    public function testThatUidIsExpectedValue(DocumentLink $link) : void
    {
        $this->assertSame('uid', $link->uid());
    }

    /** @depends testConstructor */
    public function testThatTypeIsExpectedValue(DocumentLink $link) : void
    {
        $this->assertSame('type', $link->type());
    }

    /** @depends testConstructor */
    public function testThatLanguageIsExpectedValue(DocumentLink $link) : void
    {
        $this->assertSame('en-gb', $link->language());
    }

    /** @depends testConstructor */
    public function testThatIsBrokenIsExpectedValue(DocumentLink $link) : void
    {
        $this->assertFalse($link->isBroken());
    }

    /** @depends testConstructor */
    public function testThatALinkIsNotConsideredEmpty(DocumentLink $link) : void
    {
        $this->assertFalse($link->isEmpty());
    }

    /** @depends testConstructor */
    public function testThatTagsHaveExpectedMembers(DocumentLink $link) : void
    {
        $this->assertContainsEquals('a', $link->tags());
        $this->assertContainsEquals('b', $link->tags());
    }
}
