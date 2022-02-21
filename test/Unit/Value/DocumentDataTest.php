<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use DateInterval;
use DateTimeImmutable;
use Prismic\Document\Fragment\BooleanFragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\DateFragment;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\Slice;
use Prismic\Json;
use Prismic\Value\DocumentData;
use Prismic\Value\Translation;
use PrismicTest\Framework\TestCase;

use function assert;
use function reset;

class DocumentDataTest extends TestCase
{
    /** @var DocumentData */
    private $document;
    /** @var DocumentData */
    private $documentWithUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->document = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document.json')
            )
        );

        $this->documentWithUrl = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document-with-url.json')
            )
        );
    }

    public function testId(): void
    {
        $this->assertSame('document-id', $this->document->id());
    }

    public function testUid(): void
    {
        $this->assertSame('document-uid', $this->document->uid());
    }

    public function testType(): void
    {
        $this->assertSame('custom-type', $this->document->type());
    }

    public function testLang(): void
    {
        $this->assertSame('en-gb', $this->document->lang());
    }

    public function testFirstPublished(): void
    {
        $this->assertSame(
            '2020-01-01 01:23:45 0',
            $this->document->firstPublished()->format('Y-m-d H:i:s Z')
        );
    }

    public function testLastPublished(): void
    {
        $this->assertSame(
            '2020-01-02 01:23:45 0',
            $this->document->lastPublished()->format('Y-m-d H:i:s Z')
        );
    }

    public function testTags(): void
    {
        $this->assertContainsEquals('tag-1', $this->document->tags());
        $this->assertContainsEquals('tag-2', $this->document->tags());
    }

    public function testThatDocumentBodyHasExpectedColourFragment(): void
    {
        $colour = $this->document->content()->get('colour');
        $this->assertInstanceOf(Color::class, $colour);
        $this->assertSame('#c94949', (string) $colour);
    }

    public function testThatDocumentBodyHasExpectedNumber(): void
    {
        $number = $this->document->content()->get('integer');
        assert($number instanceof Number);
        $this->assertSame(10, $number->toInteger());
    }

    public function testThatDocumentBodyHasExpectedDateFragment(): void
    {
        $date = $this->document->content()->get('date');
        assert($date instanceof DateFragment);
        $this->assertSame(
            '2020-02-03 00:00:00 0',
            $date->format('Y-m-d H:i:s Z')
        );
    }

    public function testThatDocumentBodyHasExpectedDateTimeFragment(): void
    {
        $date = $this->document->content()->get('datetime');
        assert($date instanceof DateFragment);
        $this->assertSame(
            '2020-02-03 12:13:14 0',
            $date->format('Y-m-d H:i:s Z')
        );
    }

    public function testThatDocumentBodyHasExpectedBooleanValue(): void
    {
        $bool = $this->document->content()->get('boolean');
        assert($bool instanceof BooleanFragment);
        $this->assertTrue($bool());
    }

    public function testThatDocumentBodyHasSingleImageFragment(): void
    {
        $image = $this->document->content()->get('single-image');
        assert($image instanceof Image);
        $this->assertSame('Star', $image->alt());
        $this->assertSame($image, $image->getView('main'));
    }

    public function testThatDocumentBodyHasImageWithMultipleSizes(): void
    {
        $image = $this->document->content()->get('multi-image');
        assert($image instanceof Image);
        $view = $image->getView('view-2');
        $this->assertNotSame($image, $view);
        $view = $image->getView('view-3');
        $this->assertNotSame($image, $view);
    }

    public function testThatTheSliceZoneIsACollection(): void
    {
        $slices = $this->document->content()->get('slice-zone');
        self::assertInstanceOf(Collection::class, $slices);
        $slice = $slices->slicesOfType('quote')->first();
        self::assertInstanceOf(Slice::class, $slice);
    }

    public function testThatTranslationsContainsTheExpectedValue(): void
    {
        $translations = $this->document->translations();
        $this->assertCount(1, $translations);
        $value = reset($translations);
        assert($value instanceof Translation);
        $this->assertSame('translated-id', $value->documentId());
        $this->assertSame('translated-uid', $value->documentUid());
        $this->assertSame('custom-type', $value->documentType());
        $this->assertSame('en-us', $value->language());
    }

    public function testThatDataMethodReturnsSelf(): void
    {
        $this->assertSame($this->document, $this->document->data());
    }

    public function testThatNowIsUsedForPublicationDatesWhenThePayloadIsNull(): void
    {
        $data = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document-lacking-pub-date.json')
            )
        );

        $minute = new DateInterval('PT60S');
        $now = (new DateTimeImmutable('now'))->sub($minute);
        $then = (new DateTimeImmutable('now'))->add($minute);

        $this->assertGreaterThanOrEqual($now, $data->firstPublished());
        $this->assertLessThanOrEqual($then, $data->firstPublished());
        $this->assertGreaterThanOrEqual($now, $data->lastPublished());
        $this->assertLessThanOrEqual($then, $data->lastPublished());
    }

    public function testThatADocumentWithoutAUrlPropertyWillHaveANullUrl(): void
    {
        self::assertNull($this->document->url());
    }

    public function testThatADocumentWithANullUrlWillHaveANullUrl(): void
    {
        $document = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document-with-null-url.json')
            )
        );
        self::assertNull($document->url());
    }

    public function testThatADocumentWithAStringUrlWillReturnExpectedValue(): void
    {
        self::assertSame('/example/url', $this->documentWithUrl->url());
    }

    public function testThatADocumentWithAStringUrlWillReturnTheCorrectUrlWhenCastToALink(): void
    {
        self::assertSame('/example/url', $this->documentWithUrl->asLink()->url());
    }
}
