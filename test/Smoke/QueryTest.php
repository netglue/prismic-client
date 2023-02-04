<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Prismic\Api;

class QueryTest extends TestCase
{
    /** @dataProvider apiDataProvider */
    public function testThatOrderingResultsIsPossible(Api $api): void
    {
        $query = $api->createQuery()
            ->resultsPerPage(10);

        $unordered = $api->query($query);
        if ($unordered->totalResults() <= 5) {
            $this->markTestSkipped('There are not enough documents in this repository to effectively test date based ordering');
        }

        $ordered = $api->query($query->order('document.first_publication_date'));
        $last = null;
        foreach ($ordered as $document) {
            if (! $last) {
                $last = $document;
                continue;
            }

            $this->assertGreaterThanOrEqual($last->firstPublished()->getTimestamp(), $document->firstPublished()->getTimestamp());
        }

        $reversed = $api->query($query->order('document.first_publication_date desc'));
        $last = null;
        foreach ($reversed as $document) {
            if (! $last) {
                $last = $document;
                continue;
            }

            $this->assertLessThanOrEqual($last->firstPublished()->getTimestamp(), $document->firstPublished()->getTimestamp());
        }
    }
}
