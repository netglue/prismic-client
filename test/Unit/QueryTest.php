<?php

declare(strict_types=1);

namespace PrismicTest;

use Prismic\Json;
use Prismic\Predicate;
use Prismic\Query;
use Prismic\Value\FormSpec;
use PrismicTest\Framework\TestCase;

use function array_merge;
use function sprintf;
use function substr_count;
use function urlencode;

class QueryTest extends TestCase
{
    private static object|null $formData = null;

    /**
     * @return object{everything: object, withQuery: object, collection: object}
     *
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    private static function formData(): object
    {
        if (! self::$formData) {
            self::$formData = Json::decodeObject(self::jsonFixtureByFileName('forms.json'));
        }

        return self::$formData;
    }

    /** @return array<string, array{0: Query}> */
    public static function queryWithoutDefaultQueryProvider(): array
    {
        return [
            'Standard Form' => [
                new Query(FormSpec::factory('everything', self::formData()->everything)),
            ],
            'With Query' => [
                new Query(FormSpec::factory('withQuery', self::formData()->withQuery)),
            ],
        ];
    }

    /** @return array{Collection: array{0: Query}} */
    public static function queryWithDefaultQueryProvider(): array
    {
        return [
            'Collection' => [
                new Query(FormSpec::factory('collection', self::formData()->collection)),
            ],
        ];
    }

    /** @return array<string, array{0: Query}> */
    public static function queryProvider(): array
    {
        return array_merge(
            self::queryWithoutDefaultQueryProvider(),
            self::queryWithDefaultQueryProvider(),
        );
    }

    /** @return array<string, array{0: Query, 1: string}> */
    public static function defaultUrlProvider(): array
    {
        return [
            'Standard Form' => [
                new Query(FormSpec::factory('everything', self::formData()->everything)),
                'https://example.com/api/v2?page=1&pageSize=20',
            ],
            'With Query' => [
                new Query(FormSpec::factory('withQuery', self::formData()->withQuery)),
                'https://example.com/?term=something&page=1&pageSize=20',
            ],
            'Collection' => [
                new Query(FormSpec::factory('collection', self::formData()->collection)),
                sprintf('https://example.com?q=%s&page=1&pageSize=20', urlencode('[[:d = any(document.type, ["doc-type"])]]')),
            ],
        ];
    }

    /** @dataProvider defaultUrlProvider */
    public function testDefaultUrl(Query $query, string $expectedUrl): void
    {
        $this->assertSame($expectedUrl, $query->toUrl());
    }

    /** @return array<string, array{0: Query, 1: string}> */
    public static function queryUrlProvider(): array
    {
        return [
            'Standard Form' => [
                new Query(FormSpec::factory('everything', self::formData()->everything)),
                'https://example.com/api/v2?page=1&pageSize=20&q=foo',
            ],
            'With Query' => [
                new Query(FormSpec::factory('withQuery', self::formData()->withQuery)),
                'https://example.com/?term=something&page=1&pageSize=20&q=foo',
            ],
            'Collection' => [
                new Query(FormSpec::factory('collection', self::formData()->collection)),
                sprintf('https://example.com?q=%s&q=foo&page=1&pageSize=20', urlencode('[[:d = any(document.type, ["doc-type"])]]')),
            ],
        ];
    }

    /** @dataProvider queryUrlProvider */
    public function testThatQueryValueIsAppendedToQueryString(Query $query, string $expectedUrl): void
    {
        $clone = $query->set('q', 'foo');
        $this->assertSame($expectedUrl, $clone->toUrl());
        $this->assertNotSame($query, $clone);
        $this->assertNotSame($query->toUrl(), $clone->toUrl());
    }

    /** @dataProvider queryProvider */
    public function testThatSettingResultsPerPageAltersUrl(Query $query): void
    {
        $clone = $query->resultsPerPage(99);
        $this->assertStringContainsString(
            'pageSize=20',
            $query->toUrl(),
        );
        $this->assertStringContainsString(
            'pageSize=99',
            $clone->toUrl(),
        );
        $this->assertStringNotContainsString(
            'pageSize=20',
            $clone->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatSettingAfterAltersUrl(Query $query): void
    {
        $this->assertStringNotContainsString(
            'after=DOC_ID',
            $query->toUrl(),
        );
        $this->assertStringContainsString(
            'after=DOC_ID',
            $query->after('DOC_ID')->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testSettingPageNumberAltersUrl(Query $query): void
    {
        $this->assertStringContainsString(
            'page=1',
            $query->toUrl(),
        );
        $this->assertStringContainsString(
            'page=99',
            $query->page(99)->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchLinksIsNotInitiallySet(Query $query): void
    {
        $this->assertStringNotContainsString(
            'fetchLinks',
            $query->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchIsNotInitiallySet(Query $query): void
    {
        $this->assertStringNotContainsString(
            'fetch',
            $query->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchCanBeProvidedWithStringArguments(Query $query): void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetch('first', 'second')->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchCanBeProvidedWithIterableArgument(Query $query): void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetch(...['first', 'second'])->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchLinksCanBeProvidedWithIterableArgument(Query $query): void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetchLinks(...['first', 'second'])->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatCallingFetchWithoutArgumentsRemovesParameterFromUrl(Query $query): void
    {
        $this->assertStringNotContainsString(
            'fetch=',
            $query->fetch('first', 'second')->fetch()->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatCallingFetchLinksWithoutArgumentsRemovesParameterFromUrl(Query $query): void
    {
        $this->assertStringNotContainsString(
            'fetchLinks=',
            $query->fetchLinks('first', 'second')->fetchLinks()->toUrl(),
        );
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testQueriesForFormsWithADefaultQueryWillContainQueryInUrl(Query $query): void
    {
        $this->assertStringContainsString('q=', $query->toUrl());
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testThatQueriesAreAppendedToDefaultQuery(Query $query): void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);

        $query = $query->query($predicate);

        $this->assertStringContainsString($expect, $query->toUrl());
        $this->assertSame(2, substr_count($query->toUrl(), 'q='));
    }

    /** @dataProvider queryProvider */
    public function testThatSettingTheQueryWillAlterTheUrl(Query $query): void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);
        $this->assertStringContainsString($expect, $query->query($predicate)->toUrl());
    }

    /** @dataProvider queryWithoutDefaultQueryProvider */
    public function testThatSettingEmptyPredicatesWillRemoveExistingQuery(Query $query): void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);
        $query = $query->query($predicate)->query();
        $this->assertStringNotContainsString($expect, $query->toUrl());
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testThatSettingEmptyPredicatesDoesNotRemoveDefaultQuery(Query $query): void
    {
        $query = $query->query();
        $this->assertStringContainsString('q=', $query->toUrl());
    }

    /** @dataProvider queryProvider */
    public function testThatOrderIsImplodedWithSquareBrackets(Query $query): void
    {
        $expect = urlencode('[a,b,c]');
        $this->assertStringContainsString(
            $expect,
            $query->order('a', 'b', 'c')->toUrl(),
        );
    }

    /** @dataProvider queryProvider */
    public function testThatOrderCanBeRemoved(Query $query): void
    {
        $expect = urlencode('[a,b,c]');
        $this->assertStringNotContainsString(
            $expect,
            $query->order('a', 'b', 'c')->order()->toUrl(),
        );
    }
}
