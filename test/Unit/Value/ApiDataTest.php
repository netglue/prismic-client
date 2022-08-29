<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Exception\UnexpectedValue;
use Prismic\Exception\UnknownBookmark;
use Prismic\Exception\UnknownForm;
use Prismic\Json;
use Prismic\Value\ApiData;
use Prismic\Value\Bookmark;
use Prismic\Value\Language;
use Prismic\Value\Type;
use PrismicTest\Framework\TestCase;

use function sprintf;

/** @psalm-suppress DeprecatedClass, DeprecatedMethod */
class ApiDataTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    protected function setUp(): void
    {
        parent::setUp();
        $payload = $this->jsonFixtureByFileName('api-data.json');
        $this->apiData = ApiData::factory(Json::decodeObject($payload));
    }

    /** @psalm-suppress DeprecatedMethod */
    public function testTagsHaveExpectedValue(): void
    {
        self::assertContainsEquals('goats', $this->apiData->tags());
        self::assertContainsEquals('cheese', $this->apiData->tags());
        self::assertContainsEquals('muppets', $this->apiData->tags());
    }

    public function testThatTheExpectedFormsArePresent(): void
    {
        self::assertTrue($this->apiData->hasForm('everything'));
        self::assertFalse($this->apiData->hasForm('not-found'));
    }

    public function testThatAFormIsReturnedForAKnownKey(): void
    {
        $this->apiData->form('everything');
        $this->expectNotToPerformAssertions();
    }

    public function testThatAnExceptionIsThrownRetrievingAnUnknownForm(): void
    {
        $this->expectException(UnknownForm::class);
        $this->apiData->form('not-found');
    }

    public function testThatTheMasterRefCanBeRetrieved(): void
    {
        $this->apiData->master();
        $this->expectNotToPerformAssertions();
    }

    public function testThatMissingMasterRefIsExceptional(): void
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data-missing-master.json')));
        $this->expectException(UnexpectedValue::class);
        $data->master();
    }

    public function testIsBookmarked(): void
    {
        self::assertFalse($this->apiData->isBookmarked('unknown-document'));
        self::assertTrue($this->apiData->isBookmarked('bookmarked-document-id'));
    }

    public function testBookmarkWillReturnExpectedValue(): void
    {
        $bookmark = $this->apiData->bookmark('other-bookmark');
        self::assertSame('other-bookmark', $bookmark->name());
    }

    public function testExceptionThrownRetrievingUnknownBookmark(): void
    {
        $this->expectException(UnknownBookmark::class);
        $this->apiData->bookmark('not-found');
    }

    /** @psalm-suppress DeprecatedMethod */
    public function testThatAnEmptyTagsArrayIsAcceptable(): void
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data-with-empty-tags.json')));
        self::assertEmpty($data->tags());
    }

    /** @psalm-suppress DeprecatedMethod */
    public function testThatAMissingTagsPropertyInTheDataPayloadIsAcceptable(): void
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data-missing-tags.json')));
        self::assertEmpty($data->tags());
    }

    public function testThatTypesAreTheExpectedValue(): void
    {
        self::assertContainsOnlyInstancesOf(Type::class, $this->apiData->types());
        self::assertCount(1, $this->apiData->types());
    }

    public function testThatBookmarksAreTheExpectedValue(): void
    {
        self::assertContainsOnlyInstancesOf(Bookmark::class, $this->apiData->bookmarks());
        self::assertCount(2, $this->apiData->bookmarks());
    }

    public function testThatLanguagesAreTheExpectedValue(): void
    {
        self::assertContainsOnlyInstancesOf(Language::class, $this->apiData->languages());
        self::assertCount(2, $this->apiData->languages());
    }

    /** @depends testThatLanguagesAreTheExpectedValue */
    public function testThatLanguagesContainsTheExpectedInstances(): void
    {
        $expect = ['en-gb', 'en-au'];
        foreach ($this->apiData->languages() as $language) {
            self::assertContains(
                $language->id(),
                $expect,
                sprintf('Language "%s" is not present in the expected list of values', $language->id()),
            );
        }
    }
}
