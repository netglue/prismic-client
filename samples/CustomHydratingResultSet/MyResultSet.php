<?php
declare(strict_types=1);

namespace Prismic\Example\CustomHydratingResultSet;

use Prismic\Document;
use Prismic\ResultSet;
use Prismic\ResultSet\TypicalResultSetBehaviour;
use function array_merge;
use function count;
use function max;

class MyResultSet implements ResultSet
{
    /**
     * Import the trait providing common, required methods for pagination etc.
     */
    use TypicalResultSetBehaviour;

    /** @param Document[] $results */
    public function __construct(
        int $page,
        int $resultsPerPage,
        int $totalResults,
        int $pageCount,
        ?string $nextPage,
        ?string $previousPage,
        array $results
    ) {
        $this->page = $page;
        $this->perPage = $resultsPerPage;
        $this->totalResults = $totalResults;
        $this->pageCount = $pageCount;
        $this->nextPage = $nextPage;
        $this->prevPage = $previousPage;
        $this->results = $results;
    }

    public function merge(ResultSet $with) : ResultSet
    {
        $results = array_merge($this->results, $with->results());

        return new static(
            1,
            count($results),
            $this->totalResults,
            max($this->pageCount - 1, 1),
            $with->nextPage(),
            $this->prevPage,
            $results
        );
    }
}
