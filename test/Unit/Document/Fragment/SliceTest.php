<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\Depends;
use Prismic\Document\Fragment\Slice;
use Prismic\Document\FragmentCollection;
use Prismic\Json;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;

class SliceTest extends TestCase
{
    private FragmentCollection $slices;

    protected function setUp(): void
    {
        parent::setUp();

        $document = DocumentData::factory(Json::decodeObject($this->jsonFixtureByFileName('basic-slices.json')));
        $slice = $document->content()->get('slice-zone');
        self::assertInstanceOf(FragmentCollection::class, $slice);

        $this->slices = $slice;
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

    public function testThatASharedSliceCanBeFound(): Slice
    {
        self::assertInstanceOf(FragmentCollection::class, $this->slices);
        $slice = $this->slices->filter(static function (Slice $slice): bool {
            return $slice->type() === 'shared_slice';
        })->first();
        self::assertInstanceOf(Slice::class, $slice);

        return $slice;
    }

    public function testThatASharedSliceCanBeFoundWithAnEmptyVersionString(): Slice
    {
        self::assertInstanceOf(FragmentCollection::class, $this->slices);
        $slice = $this->slices->filter(static function (Slice $slice): bool {
            return $slice->type() === 'shared_slice_empty_version';
        })->first();
        self::assertInstanceOf(Slice::class, $slice);

        return $slice;
    }

    #[Depends('testThatASliceCanBeFound')]
    public function testThatTheLabelIsTheExpectedValue(Slice $slice): void
    {
        self::assertEquals('custom-label', $slice->label());
    }

    #[Depends('testThatASliceCanBeFound')]
    public function testThatTheSliceIsNotEmpty(Slice $slice): void
    {
        self::assertFalse($slice->isEmpty());
    }

    #[Depends('testThatAnEmptySliceCanBeFound')]
    public function testThatTheEmptySliceIsEmpty(Slice $slice): void
    {
        self::assertTrue($slice->isEmpty());
    }

    #[Depends('testThatASliceCanBeFound')]
    public function testThatToStringWillYieldTheExpectedValue(Slice $slice): void
    {
        $expect = <<<'TEXT'
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

    #[Depends('testThatASliceCanBeFound')]
    public function testSharedSlicePropertiesAreNullForRegularSlices(Slice $slice): void
    {
        self::assertNull($slice->id());
        self::assertNull($slice->variation());
        self::assertNull($slice->version());
    }

    #[Depends('testThatASharedSliceCanBeFound')]
    public function testSharedSlicesHaveAdditionalProperties(Slice $slice): void
    {
        self::assertNotEmpty($slice->id());
        self::assertNotEmpty($slice->variation());
        self::assertNotEmpty($slice->version());
    }

    #[Depends('testThatASharedSliceCanBeFoundWithAnEmptyVersionString')]
    public function testSliceWithEmptyVersionWillHaveNullVersion(Slice $slice): void
    {
        self::assertNotEmpty($slice->id());
        self::assertNotEmpty($slice->variation());
        self::assertNull($slice->version());
    }
}
