<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use ArrayIterator;
use Prismic\Document;
use Traversable;

use function count;
use function reset;

/** @template T of Document */
trait TypicalResultSetBehaviour
{
    /** @var list<T> */
    private array $results;
    private int $page;
    private int $perPage;
    private int $totalResults;
    private int $pageCount;
    private string|null $nextPage;
    private string|null $prevPage;

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

    public function nextPage(): string|null
    {
        return $this->nextPage;
    }

    public function previousPage(): string|null
    {
        return $this->prevPage;
    }

    /** @return list<T> */
    public function results(): array
    {
        return $this->results;
    }

    /** @return Traversable<array-key, T> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    /** @psalm-return T|null */
    public function first(): Document|null
    {
        $first = reset($this->results);

        return $first instanceof Document ? $first : null;
    }

    public function count(): int
    {
        return count($this->results);
    }
}
