<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Exception\UnexpectedValue;
use Prismic\Exception\UnknownBookmark;
use Prismic\Exception\UnknownForm;
use Prismic\Json;
use Prismic\Value\ApiData;
use PrismicTest\Framework\TestCase;

class ApiDataTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    protected function setUp() : void
    {
        parent::setUp();
        $payload = $this->jsonFixtureByFileName('api-data.json');
        $this->apiData = ApiData::factory(Json::decodeObject($payload));
    }

    public function testTagsHaveExpectedValue() : void
    {
        $this->assertContainsEquals('goats', $this->apiData->tags());
        $this->assertContainsEquals('cheese', $this->apiData->tags());
        $this->assertContainsEquals('muppets', $this->apiData->tags());
    }

    public function testThatTheExpectedFormsArePresent() : void
    {
        $this->assertTrue($this->apiData->hasForm('everything'));
        $this->assertFalse($this->apiData->hasForm('not-found'));
    }

    public function testThatAFormIsReturnedForAKnownKey() : void
    {
        $this->apiData->form('everything');
        $this->addToAssertionCount(1);
    }

    public function testThatAnExceptionIsThrownRetrievingAnUnknownForm() : void
    {
        $this->expectException(UnknownForm::class);
        $this->apiData->form('not-found');
    }

    public function testThatTheMasterRefCanBeRetrieved() : void
    {
        $this->apiData->master();
        $this->addToAssertionCount(1);
    }

    public function testThatMissingMasterRefIsExceptional() : void
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data-missing-master.json')));
        $this->expectException(UnexpectedValue::class);
        $data->master();
    }

    public function testIsBookmarked() : void
    {
        $this->assertFalse($this->apiData->isBookmarked('unknown-document'));
        $this->assertTrue($this->apiData->isBookmarked('bookmarked-document-id'));
    }

    public function testBookmarkWillReturnExpectedValue() : void
    {
        $bookmark = $this->apiData->bookmark('other-bookmark');
        $this->assertSame('other-bookmark', $bookmark->name());
    }

    public function testExceptionThrownRetrievingUnknownBookmark() : void
    {
        $this->expectException(UnknownBookmark::class);
        $this->apiData->bookmark('not-found');
    }
}
