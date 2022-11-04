<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Document\Fragment\BooleanFragment;
use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\DateFragment;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\EmptyFragment;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\Fragment\GeoPoint;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\StringFragment;
use Prismic\Document\FragmentCollection;
use Prismic\Exception\InvalidArgument;
use Prismic\Exception\UnexpectedValue;
use Prismic\Json;
use PrismicTest\Framework\TestCase;

use function assert;

class FactoryTest extends TestCase
{
    private function imageFixture(): FragmentCollection
    {
        $collection = Factory::factory(Json::decodeObject($this->jsonFixtureByFileName('images.json')));
        assert($collection instanceof FragmentCollection);

        return $collection;
    }

    /** @return array<string, array{0: scalar|null, 1: class-string}> */
    public function scalarTypes(): iterable
    {
        return [
            'integer'    => [1, Number::class],
            'float'      => [0.123, Number::class],
            'bool'       => [true, BooleanFragment::class],
            'string'     => ['whatever', StringFragment::class],
            'null'       => [null, EmptyFragment::class],
            'hex colour' => ['#000000', Color::class],
            'Y-m-d'      => ['2020-01-01', DateFragment::class],
            'Date Time'  => ['2020-01-01T10:00:00+00:00', DateFragment::class],
        ];
    }

    /**
     * @param class-string $expectedType
     *
     * @dataProvider scalarTypes
     */
    public function testScalarValues(string|int|float|bool|null $value, string $expectedType): void
    {
        $fragment = Factory::factory($value);
        $this->assertInstanceOf($expectedType, $fragment);
    }

    public function testThatALinkWithOnlyALinkTypeSpecifiedIsTreatedAsAnEmptyFragment(): void
    {
        $link = Json::decodeObject('{
            "link_type": "Document"
        }');

        $fragment = Factory::factory($link);
        $this->assertInstanceOf(EmptyFragment::class, $fragment);
    }

    public function testUnknownLinkTypeIsExceptional(): void
    {
        $link = Json::decodeObject('{
            "link_type": "Not Right",
            "some_other_property" : "is required to avoid skipping an invalid link"
        }');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('The link type "Not Right" is not a known type of link');
        Factory::factory($link);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public function exceptionalImageSpecs(): array
    {
        return [
            'Dimensions not object' => [
                '{"dimensions":"foo"}',
                'Expected the object property "dimensions" to be a object',
            ],
            'Non Integer Width' => [
                '{"dimensions":{"width":"foo","height":100},"url":"URL"}',
                'Expected the object property "width" to be a integer',
            ],
            'Non Integer Height' => [
                '{"dimensions":{"width":100,"height":"foo"},"url":"URL"}',
                'Expected the object property "height" to be a integer',
            ],
            'Missing URL' => [
                '{"dimensions":{"width":100,"height":100}}',
                'Expected an object to contain the property "url"',
            ],
            'Non string URL' => [
                '{"dimensions":{"width":100,"height":100}, "url":null}',
                'Expected the object property "url" to be a string',
            ],
            'Non string Alt' => [
                '{"dimensions":{"width":100,"height":100}, "url":"foo", "alt":1}',
                'Expected the object property "alt" to be a string or null',
            ],
            'Non string copyright' => [
                '{"dimensions":{"width":100,"height":100}, "url":"foo", "alt":"foo","copyright":1}',
                'Expected the object property "copyright" to be a string or null',
            ],
        ];
    }

    /** @dataProvider exceptionalImageSpecs */
    public function testInvalidImageSpecsAreExceptional(string $json, string $expectedMessage): void
    {
        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage($expectedMessage);
        Factory::factory(Json::decodeObject($json));
    }

    public function testImageFactoryCanCreateRegularImage(): void
    {
        $collection = $this->imageFixture();
        $this->assertInstanceOf(Image::class, $collection->get('single_image'));
    }

    public function testImageFactoryCanCreateImageWithMultipleViews(): void
    {
        $collection = $this->imageFixture();
        $image = $collection->get('image_with_views');
        assert($image instanceof Image);
        $this->assertCount(3, $image);
        $this->assertInstanceOf(Image::class, $image->getView('view-one'));
        $this->assertInstanceOf(Image::class, $image->getView('view-two'));
    }

    public function testImageInRichTextIsImageFragment(): void
    {
        $collection = $this->imageFixture();
        $richText = $collection->get('rich_text');
        assert($richText instanceof FragmentCollection);
        $image = $richText->filter(static function (Fragment $fragment): bool {
            return $fragment instanceof Image;
        })->first();
        assert($image instanceof Image);
        $this->assertSame('https://example.com/image-in-rich-text.gif', $image->url());
    }

    public function testLinkedImageInRichTextIsImageFragment(): void
    {
        $collection = $this->imageFixture();
        $richText = $collection->get('rich_text');
        assert($richText instanceof FragmentCollection);
        $images = $richText->filter(static function (Fragment $fragment): bool {
            return $fragment instanceof Image;
        });
        $this->assertTrue($images->has(1));
        $image = $images->get(1);
        assert($image instanceof Image);
        $this->assertNotNull($image->linkTo());
    }

    public function testThatAGeoPointCanBeDecoded(): void
    {
        $fragment = Factory::factory(Json::decodeObject(<<<'JSON'
            {
                "latitude": 0.12345,
                "longitude": -1.23456
            }
            JSON));

        self::assertInstanceOf(GeoPoint::class, $fragment);
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        assert($fragment instanceof GeoPoint);

        self::assertEquals(0.12345, $fragment->latitude());
        self::assertEquals(-1.23456, $fragment->longitude());
    }

    public function testThatAGeoPointCanBeDecodedIfThePayloadContainsIntegers(): void
    {
        $fragment = Factory::factory(Json::decodeObject(<<<'JSON'
            {
                "latitude": 0,
                "longitude": 1
            }
            JSON));

        self::assertInstanceOf(GeoPoint::class, $fragment);
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        assert($fragment instanceof GeoPoint);

        self::assertEquals(0.0, $fragment->latitude());
        self::assertEquals(1.0, $fragment->longitude());
    }

    public function testThatAGeoPointIsNotDecodedIfTheValuesAreNotNumeric(): void
    {
        $this->expectException(UnexpectedValue::class);
        Factory::factory(Json::decodeObject(<<<'JSON'
            {
                "latitude": "goat",
                "longitude": "brains"
            }
            JSON));
    }

    public function testThatABrokenDocumentLinkWithoutALanguageWillHaveAWildCardLanguageSet(): void
    {
        $data = Json::decodeObject('{
            "link_type": "Document",
            "id": "foo",
            "uid": "bar",
            "type": "bing",
            "isBroken": true,
            "tags": []
        }');

        $link = Factory::factory($data);
        assert($link instanceof DocumentLink);

        self::assertEquals('*', $link->language());
    }

    public function testThatADocumentLinkWithoutALanguageWillHaveAWildCardLanguageSet(): void
    {
        $data = Json::decodeObject('{
            "link_type": "Document",
            "id": "foo",
            "uid": "bar",
            "type": "bing",
            "isBroken": false,
            "tags": []
        }');

        $link = Factory::factory($data);
        assert($link instanceof DocumentLink);

        self::assertEquals('*', $link->language());
    }

    public function testThatADocumentLinkWithNonStringTagsWillCauseAnException(): void
    {
        $data = Json::decodeObject('{
            "link_type": "Document",
            "id": "foo",
            "uid": "bar",
            "type": "bing",
            "isBroken": false,
            "tags": [1, 2, 3]
        }');

        $this->expectException(UnexpectedValue::class);

        Factory::factory($data);
    }

    public function testThatADocumentLinkWithNonArrayTagsWillCauseAnException(): void
    {
        $data = Json::decodeObject('{
            "link_type": "Document",
            "id": "foo",
            "uid": "bar",
            "type": "bing",
            "isBroken": false,
            "tags": "String"
        }');

        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('"tags"');

        Factory::factory($data);
    }

    public function testAMediaLinkWithNonNumericSizeIsExceptional(): void
    {
        $data = Json::decodeObject('{
            "link_type": "Media",
            "url": "foo",
            "name": "bar",
            "size": "bing"
        }');

        $this->expectException(UnexpectedValue::class);
        $this->expectExceptionMessage('"size"');

        Factory::factory($data);
    }

    /** @return array<string, array{0: string, 1: string|null}> */
    public function documentLinkProvider(): array
    {
        return [
            'Valid String Url' => [
                '{
                    "link_type": "Document",
                    "id": "foo",
                    "uid": "bar",
                    "url": "/something",
                    "type": "bing",
                    "isBroken": false,
                    "tags": []
                }',
                '/something',
            ],
            'Unset URL' => [
                '{
                    "link_type": "Document",
                    "id": "foo",
                    "uid": "bar",
                    "type": "bing",
                    "isBroken": false,
                    "tags": []
                }',
                null,
            ],
            'Explicit Null' => [
                '{
                    "link_type": "Document",
                    "id": "foo",
                    "uid": "bar",
                    "type": "bing",
                    "isBroken": false,
                    "tags": [],
                    "url": null
                }',
                null,
            ],
        ];
    }

    /** @dataProvider documentLinkProvider */
    public function testThatDocumentLinksWithReadyMadeUrlsWillHaveTheExpectedValue(string $json, string|null $expect): void
    {
        $data = Json::decodeObject($json);
        $link = Factory::factory($data);
        self::assertInstanceOf(DocumentLink::class, $link);
        self::assertSame($expect, $link->url());
    }

    /**
     * @param class-string $expectedType
     *
     * @dataProvider scalarTypes
     */
    public function testThatTheFactoryCanBeNewedAndInvoked(string|int|float|bool|null $value, string $expectedType): void
    {
        $factory = new Factory();
        $fragment = $factory($value);
        $this->assertInstanceOf($expectedType, $fragment);
    }
}
