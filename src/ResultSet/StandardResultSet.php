<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Prismic\Document;
use Prismic\Json;
use Prismic\ResultSet;
use Prismic\Value\DataAssertionBehaviour;
use Prismic\Value\DocumentData;
use Psr\Http\Message\ResponseInterface;

use function array_map;
use function array_merge;
use function assert;
use function count;
use function current;
use function is_string;
use function max;
use function preg_match;
use function sprintf;

/**
 * @template T of Document
 * @template-implements ResultSet<T>
 */
final class StandardResultSet implements ResultSet
{
    use DataAssertionBehaviour;
    /** @use TypicalResultSetBehaviour<T> */
    use TypicalResultSetBehaviour;

    private DateTimeInterface|null $cacheDate = null;
    private int|null $maxAge = null;

    /** @param list<T> $results */
    private function __construct(
        int $page,
        int $perPage,
        int $totalResults,
        int $pageCount,
        string|null $nextPage,
        string|null $prevPage,
        array $results,
    ) {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->totalResults = $totalResults;
        $this->pageCount = $pageCount;
        $this->nextPage = $nextPage;
        $this->prevPage = $prevPage;
        $this->results = $results;
    }

    public static function withHttpResponse(ResponseInterface $response): ResultSet
    {
        $instance = self::factory(Json::decodeObject((string) $response->getBody()));
        $dateHeader = current($response->getHeader('Date'));
        $date = is_string($dateHeader)
            ? DateTimeImmutable::createFromFormat(DateTimeInterface::RFC7231, $dateHeader, new DateTimeZone('UTC'))
            : null;
        $instance->cacheDate = $date instanceof DateTimeImmutable ? $date : null;
        $cacheControl = current($response->getHeader('Cache-Control'));
        if (preg_match('/^max-age\s*=\s*(\d+)$/', (string) $cacheControl, $groups) === 1) {
            $instance->maxAge = (int) $groups[1];
        }

        return $instance;
    }

    public static function factory(object $data): self
    {
        /** @psalm-var list<DocumentData> $results */
        $results = array_map(static function (object $document): Document {
            return DocumentData::factory($document);
        }, self::assertObjectPropertyIsArray($data, 'results'));

        return new self(
            self::assertObjectPropertyIsInteger($data, 'page'),
            self::assertObjectPropertyIsInteger($data, 'results_per_page'),
            self::assertObjectPropertyIsInteger($data, 'total_results_size'),
            self::assertObjectPropertyIsInteger($data, 'total_pages'),
            self::optionalStringProperty($data, 'next_page'),
            self::optionalStringProperty($data, 'prev_page'),
            $results,
        );
    }

    /**
     * Returns the expiry date for the API response used to create this result set.
     *
     * If the result set was not created with an HTTP response, the expiry date is not known and in this case, the
     * current date is returned.
     *
     * All dates are UTC
     */
    public function expiresAt(): DateTimeImmutable
    {
        if ($this->cacheDate === null || $this->maxAge === null) {
            return (new DateTimeImmutable())->setTimezone(new DateTimeZone('UTC'));
        }

        assert($this->cacheDate instanceof DateTimeImmutable);

        return $this->cacheDate->add(new DateInterval(sprintf('PT%dS', $this->maxAge)));
    }

    public function merge(ResultSet $with): ResultSet
    {
        $results = array_merge($this->results, $with->results());

        return new self(
            1,
            count($results),
            $this->totalResults,
            max($this->pageCount - 1, 1),
            $with->nextPage(),
            $this->prevPage,
            $results,
        );
    }
}
