<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use ArrayIterator;
use Prismic\Document;

use function count;
use function reset;

trait TypicalResultSetBehaviour
{
    /** @var Document[] */
    private $results;

    /** @var int */
    private $page;

    /** @var int */
    private $perPage;

    /** @var int */
    private $totalResults;

    /** @var int */
    private $pageCount;

    /** @var string|null */
    private $nextPage;

    /** @var string|null */
    private $prevPage;

    public function currentPageNumber(): int
    {
        return $this->page;
    }

    public function resultsPerPage(): int
    {
        return $this->perPage;
    }

    public function totalResults(): int
    {
        return $this->totalResults;
    }

    public function pageCount(): int
    {
        return $this->pageCount;
    }

    public function nextPage(): ?string
    {
        return $this->nextPage;
    }

    public function previousPage(): ?string
    {
        return $this->prevPage;
    }

    /** @return Document[] */
    public function results(): array
    {
        return $this->results;
    }

    /** @return Document[] */
    public function getIterator(): iterable
    {
        return new ArrayIterator($this->results);
    }

    public function first(): ?Document
    {
        $first = reset($this->results);

        return $first instanceof Document ? $first : null;
    }

    public function count(): int
    {
        return count($this->results);
    }
}
