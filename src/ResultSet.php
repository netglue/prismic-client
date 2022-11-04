<?php

declare(strict_types=1);

namespace Prismic;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of Document
 * @template-implements IteratorAggregate<T>
 */
interface ResultSet extends IteratorAggregate, Countable
{
    /**
     * The page number this result set represents in a paginated result
     */
    public function currentPageNumber(): int;

    /**
     * The expected number of results per page
     */
    public function resultsPerPage(): int;

    /**
     * The total number of documents found in the api that match the query
     */
    public function totalResults(): int;

    /**
     * The total number of pages in the api that match for the matching results.
     */
    public function pageCount(): int;

    /**
     * Absolute URL to retrieve the next page of results from the remote api.
     */
    public function nextPage(): string|null;

    /**
     * Absolute URL to retrieve the previous page of results from the remote api.
     */
    public function previousPage(): string|null;

    /**
     * Return the document results as an array
     *
     * @return list<T>
     */
    public function results(): array;

    /**
     * Retrieve an iterator for iterating over results
     *
     * @return Traversable<array-key, T>
     */
    public function getIterator(): Traversable;

    /**
     * Return the first document in the result set or null if the result set is empty
     *
     * @psalm-return T|null
     */
    public function first(): Document|null;

    /**
     * Merge the results of two responses together.
     *
     * The primary purpose of this method is to collect paginated results into a single response and should not be used
     * to merge unrelated result sets in {@link Api::findAll()}. If you need to combine results yourself, just use
     * $combined = array_merge($response1->results(), $response2->results());
     *
     * @param self<T> $with
     *
     * @return self<T>
     */
    public function merge(self $with): self;
}
