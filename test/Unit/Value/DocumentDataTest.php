<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Document\Fragment\BooleanFragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\DateFragment;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\Slice;
use Prismic\Json;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;
use function assert;

class DocumentDataTest extends TestCase
{
    /** @var DocumentData */
    private $document;

    protected function setUp() : void
    {
        parent::setUp();
        $this->document = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document.json')
            )
        );
    }

    public function testId() : void
    {
        $this->assertSame('document-id', $this->document->id());
    }

    public function testUid() : void
    {
        $this->assertSame('document-uid', $this->document->uid());
    }

    public function testType() : void
    {
        $this->assertSame('custom-type', $this->document->type());
    }

    public function testLang() : void
    {
        $this->assertSame('en-gb', $this->document->lang());
    }

    public function testFirstPublished() : void
    {
        $this->assertSame(
            '2020-01-01 01:23:45 0',
            $this->document->firstPublished()->format('Y-m-d H:i:s Z')
        );
    }

    public function testLastPublished() : void
    {
        $this->assertSame(
            '2020-01-02 01:23:45 0',
            $this->document->lastPublished()->format('Y-m-d H:i:s Z')
        );
    }

    public function testTags() : void
    {
        $this->assertContainsEquals('tag-1', $this->document->tags());
        $this->assertContainsEquals('tag-2', $this->document->tags());
    }

    public function testSlugs() : void
    {
        $this->assertContainsEquals('slug-1', $this->document->slugs());
        $this->assertContainsEquals('slug-2', $this->document->slugs());
    }

    public function testThatDocumentBodyHasExpectedColourFragment() : void
    {
        $colour = $this->document->body()->get('colour');
        $this->assertInstanceOf(Color::class, $colour);
        $this->assertSame('#c94949', (string) $colour);
    }

    public function testThatDocumentBodyHasExpectedNumber() : void
    {
        $number = $this->document->body()->get('integer');
        assert($number instanceof Number);
        $this->assertSame(10, $number->toInteger());
    }

    public function testThatDocumentBodyHasExpectedDateFragment() : void
    {
        $date = $this->document->body()->get('date');
        assert($date instanceof DateFragment);
        $this->assertSame(
            '2020-02-03 00:00:00 0',
            $date->format('Y-m-d H:i:s Z')
        );
    }

    public function testThatDocumentBodyHasExpectedDateTimeFragment() : void
    {
        $date = $this->document->body()->get('datetime');
        assert($date instanceof DateFragment);
        $this->assertSame(
            '2020-02-03 12:13:14 0',
            $date->format('Y-m-d H:i:s Z')
        );
    }

    public function testThatDocumentBodyHasExpectedBooleanValue() : void
    {
        $bool = $this->document->body()->get('boolean');
        assert($bool instanceof BooleanFragment);
        $this->assertTrue($bool());
    }

    public function testThatDocumentBodyHasSingleImageFragment() : void
    {
        $image = $this->document->body()->get('single-image');
        assert($image instanceof Image);
        $this->assertSame('Star', $image->alt());
        $this->assertSame($image, $image->getView('main'));
    }

    public function testThatDocumentBodyHasImageWithMultipleSizes() : void
    {
        $image = $this->document->body()->get('multi-image');
        assert($image instanceof Image);
        $view = $image->getView('view-2');
        $this->assertNotSame($image, $view);
        $view = $image->getView('view-3');
        $this->assertNotSame($image, $view);
    }

    public function testThatTheSliceZoneIsACollection() : void
    {
        $slices = $this->document->body()->get('slice-zone');
        assert($slices instanceof Collection);
        $slice = $slices->slicesOfType('quote')->first();
        assert($slice instanceof Slice);
        $this->addToAssertionCount(2);
    }
}
