<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\Slice;
use Prismic\Document\FragmentCollection;
use Prismic\Json;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;

class SliceTest extends TestCase
{
    /** @var FragmentCollection */
    private $slices;

    protected function setUp(): void
    {
        parent::setUp();
        $document = DocumentData::factory(Json::decodeObject($this->jsonFixtureByFileName('basic-slices.json')));
        $this->slices = $document->content()->get('slice-zone');
    }

    public function testThatASliceCanBeFound(): Slice
    {
        self::assertInstanceOf(FragmentCollection::class, $this->slices);
        $slice = $this->slices->filter(static function (Slice $slice): bool {
            return $slice->type() === 'custom';
        })->first();
        self::assertInstanceOf(Slice::class, $slice);

        return $slice;
    }

    public function testThatAnEmptySliceCanBeFound(): Slice
    {
        self::assertInstanceOf(FragmentCollection::class, $this->slices);
        $slice = $this->slices->filter(static function (Slice $slice): bool {
            return $slice->type() === 'empty';
        })->first();
        self::assertInstanceOf(Slice::class, $slice);

        return $slice;
    }

    /** @depends testThatASliceCanBeFound */
    public function testThatTheLabelIsTheExpectedValue(Slice $slice): void
    {
        self::assertEquals('custom-label', $slice->label());
    }

    /** @depends testThatASliceCanBeFound */
    public function testThatTheSliceIsNotEmpty(Slice $slice): void
    {
        self::assertFalse($slice->isEmpty());
    }

    /** @depends testThatAnEmptySliceCanBeFound */
    public function testThatTheEmptySliceIsEmpty(Slice $slice): void
    {
        self::assertTrue($slice->isEmpty());
    }

    /** @depends testThatASliceCanBeFound */
    public function testThatToStringWillYieldTheExpectedValue(Slice $slice): void
    {
        $expect = <<<TEXT
            Heading 1
            Heading 2
            42
            Some Text
            43
            More Text
            TEXT;

        self::assertEquals($expect, (string) $slice);
    }

    /** @depends testThatAnEmptySliceCanBeFound */
    public function testThatTheEmptySliceIsAnEmptyStringWhenCastToAString(Slice $slice): void
    {
        self::assertEquals('', (string) $slice);
    }
}
