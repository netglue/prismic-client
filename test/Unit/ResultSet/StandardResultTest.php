<?php
declare(strict_types=1);

namespace PrismicTest\ResultSet;

use Prismic\Json;
use Prismic\ResultSet\StandardResultSet;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;

class StandardResultTest extends TestCase
{
    public function testBasicAccessors() : void
    {
        $response = StandardResultSet::factory(Json::decodeObject($this->jsonFixtureByFileName('response.json')));
        $this->assertSame(1, $response->resultsPerPage());
        $this->assertSame(1, $response->currentPageNumber());
        $this->assertSame(99, $response->totalResults());
        $this->assertSame(99, $response->pageCount());
        $this->assertNotNull($response->expiresAt());
        $this->assertSame('https://example.com/next', $response->nextPage());
        $this->assertSame('https://example.com/prev', $response->previousPage());
        $this->assertCount(1, $response->results());
        $this->assertContainsOnlyInstancesOf(DocumentData::class, $response->results());
    }
}
