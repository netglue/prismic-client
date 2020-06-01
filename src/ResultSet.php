<?php
declare(strict_types=1);

namespace Prismic;

use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;

interface ResultSet extends IteratorAggregate
{
    /**
     * Named constructor, that, when given an HTTP response can yield a ResultSet instance
     */
    public static function withHttpResponse(ResponseInterface $response) : self;

    /**
     * The page number this result set represents in a paginated result
     */
    public function currentPageNumber() : int;

    /**
     * The expected number of results per page
     */
    public function resultsPerPage() : int;

    /**
     * The total number of documents found in the api that match the query
     */
    public function totalResults() : int;

    /**
     * The total number of pages in the api that match for the matching results.
     */
    public function pageCount() : int;

    /**
     * Absolute URL to retrieve the next page of results from the remote api.
     */
    public function nextPage() :? string;

    /**
     * Absolute URL to retrieve the previous page of results from the remote api.
     */
    public function previousPage() :? string;

    /** @return Document[] */
    public function results() : array;

    /** @return Document[] */
    public function getIterator() : iterable;

    public function first() :? Document;

    /**
     * Merge the results of two responses together.
     *
     * The primary purpose of this method is to collect paginated results into a single response and should not be used
     * to merge unrelated result sets in {@link Api::findAll()}. If you need to combine results yourself, just use
     * $combined = array_merge($response1->results(), $response2->results());
     */
    public function merge(self $with) : self;
}
