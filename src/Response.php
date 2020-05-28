<?php
declare(strict_types=1);

namespace Prismic;

use ArrayIterator;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use Prismic\Value\DataAssertionBehaviour;
use Prismic\Value\DocumentData;
use Psr\Http\Message\ResponseInterface;
use function array_map;
use function current;
use function preg_match;
use function reset;

class Response implements IteratorAggregate
{
    use DataAssertionBehaviour;

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

    /** @var DocumentData[] */
    private $results;

    /** @var DateTimeImmutable|null */
    private $date;

    /** @var int|null */
    private $maxAge;

    /** @param DocumentData[] $results */
    private function __construct(
        int $page,
        int $perPage,
        int $totalResults,
        int $pageCount,
        ?string $nextPage,
        ?string $prevPage,
        iterable $results
    ) {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->totalResults = $totalResults;
        $this->pageCount = $pageCount;
        $this->nextPage = $nextPage;
        $this->prevPage = $prevPage;
        $this->results = $results;
    }

    public static function withHttpResponse(ResponseInterface $response) : self
    {
        $instance = self::factory(Json::decodeObject((string) $response->getBody()));
        $dateHeader = current($response->getHeader('Date'));
        $instance->date = $dateHeader
            ? DateTimeImmutable::createFromFormat(DateTimeInterface::RFC7231, $dateHeader)
            : null;
        $cacheControl = current($response->getHeader('Cache-Control'));
        if (preg_match('/^max-age\s*=\s*(\d+)$/', (string) $cacheControl, $groups) === 1) {
            $instance->maxAge = (int) $groups[1];
        }

        return $instance;
    }

    public static function factory(object $data) : self
    {
        $results = array_map(static function (object $document) : DocumentData {
            return DocumentData::factory($document);
        }, self::assertObjectPropertyIsArray($data, 'results'));

        return new static(
            self::assertObjectPropertyIsInteger($data, 'page'),
            self::assertObjectPropertyIsInteger($data, 'results_per_page'),
            self::assertObjectPropertyIsInteger($data, 'total_results_size'),
            self::assertObjectPropertyIsInteger($data, 'total_pages'),
            self::optionalStringProperty($data, 'next_page'),
            self::optionalStringProperty($data, 'prev_page'),
            $results
        );
    }

    public function getCurrentPageNumber() : int
    {
        return $this->page;
    }

    public function getResultsPerPage() : int
    {
        return $this->perPage;
    }

    public function getTotalResults() : int
    {
        return $this->totalResults;
    }

    public function getTotalPageCount() : int
    {
        return $this->pageCount;
    }

    public function getNextPageUrl() :? string
    {
        return $this->nextPage;
    }

    public function getPrevPageUrl() :? string
    {
        return $this->prevPage;
    }

    /** @return DocumentData[] */
    public function getResults() : array
    {
        return $this->results;
    }

    /** @return DocumentData[] */
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->results);
    }

    public function first() :? DocumentData
    {
        return reset($this->results);
    }

    public function merge(self $with) : self
    {
        // @TODO
    }
}
