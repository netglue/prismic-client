<?php

declare(strict_types=1);

namespace PrismicTest\ResultSet;

use DateTimeImmutable;
use DateTimeZone;
use Prismic\Document;
use Prismic\Json;
use Prismic\ResultSet\StandardResultSet;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;

class StandardResultTest extends TestCase
{
    private StandardResultSet $resultSet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultSet = StandardResultSet::factory(Json::decodeObject($this->jsonFixtureByFileName('response.json')));
    }

    public function testBasicAccessors(): void
    {
        $this->assertSame(1, $this->resultSet->resultsPerPage());
        $this->assertSame(1, $this->resultSet->currentPageNumber());
        $this->assertSame(99, $this->resultSet->totalResults());
        $this->assertSame(99, $this->resultSet->pageCount());
        $this->assertSame('https://example.com/next', $this->resultSet->nextPage());
        $this->assertSame('https://example.com/prev', $this->resultSet->previousPage());
        $this->assertCount(2, $this->resultSet->results());
        $this->assertContainsOnlyInstancesOf(DocumentData::class, $this->resultSet->results());
    }

    public function testThatTheCacheExpiryDateWillBeTheCurrentTimeInUTC(): void
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        self::assertInstanceOf(DateTimeImmutable::class, $now);

        self::assertEqualsWithDelta($now, $this->resultSet->expiresAt(), 0.0001);
        self::assertEquals('UTC', $this->resultSet->expiresAt()->getTimezone()->getName());
    }

    public function testThatResultSetsAreCountable(): void
    {
        $this->assertCount(2, $this->resultSet);
    }

    public function testResultSetIsIterable(): void
    {
        $c = 0;
        foreach ($this->resultSet as $item) {
            $this->assertInstanceOf(Document::class, $item);
            $c++;
        }

        $this->assertGreaterThanOrEqual(1, $c, 'No iterations occurred');
    }
}
