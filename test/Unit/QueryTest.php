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
    /** @var object */
    private $formData;

    private function formData() : object
    {
        if (! $this->formData) {
            $this->formData = Json::decodeObject($this->jsonFixtureByFileName('forms.json'));
        }

        return $this->formData;
    }

    /** @return mixed[] */
    public function queryWithoutDefaultQueryProvider() : iterable
    {
        return [
            'Standard Form' => [
                new Query(FormSpec::factory('everything', $this->formData()->everything)),
            ],
            'With Query' => [
                new Query(FormSpec::factory('withQuery', $this->formData()->withQuery)),
            ],
        ];
    }

    /** @return mixed[] */
    public function queryWithDefaultQueryProvider() : iterable
    {
        return [
            'Collection' => [
                new Query(FormSpec::factory('collection', $this->formData()->collection)),
            ],
        ];
    }

    /** @return mixed[] */
    public function queryProvider() : iterable
    {
        return array_merge(
            $this->queryWithoutDefaultQueryProvider(),
            $this->queryWithDefaultQueryProvider(),
        );
    }

    /** @return mixed[] */
    public function defaultUrlProvider() : iterable
    {
        $queries = $this->queryProvider();
        $queries['Standard Form'][1] = 'https://example.com/api/v2?page=1&pageSize=20';
        $queries['Collection'][1] = sprintf('https://example.com?q=%s&page=1&pageSize=20', urlencode('[[:d = any(document.type, ["doc-type"])]]'));
        $queries['With Query'][1] = 'https://example.com/?term=something&page=1&pageSize=20';

        return $queries;
    }

    /**
     * @dataProvider defaultUrlProvider
     */
    public function testDefaultUrl(Query $query, string $expectedUrl) : void
    {
        $this->assertSame($expectedUrl, $query->toUrl());
    }

    /** @return mixed[] */
    public function queryUrlProvider() : iterable
    {
        $queries = $this->queryProvider();
        $queries['Standard Form'][1] = 'https://example.com/api/v2?page=1&pageSize=20&q=foo';
        $queries['Collection'][1] = sprintf('https://example.com?q=%s&q=foo&page=1&pageSize=20', urlencode('[[:d = any(document.type, ["doc-type"])]]'));
        $queries['With Query'][1] = 'https://example.com/?term=something&page=1&pageSize=20&q=foo';

        return $queries;
    }

    /** @dataProvider queryUrlProvider */
    public function testThatQueryValueIsAppendedToQueryString(Query $query, string $expectedUrl) : void
    {
        $query = $query->set('q', 'foo');
        $this->assertSame($expectedUrl, $query->toUrl());
    }

    /** @dataProvider queryProvider */
    public function testThatSettingResultsPerPageAltersUrl(Query $query) : void
    {
        $this->assertStringContainsString(
            'pageSize=20',
            $query->toUrl()
        );
        $this->assertStringContainsString(
            'pageSize=99',
            $query->resultsPerPage(99)->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatSettingAfterAltersUrl(Query $query) : void
    {
        $this->assertStringNotContainsString(
            'after=DOC_ID',
            $query->toUrl()
        );
        $this->assertStringContainsString(
            'after=DOC_ID',
            $query->after('DOC_ID')->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testSettingPageNumberAltersUrl(Query $query) : void
    {
        $this->assertStringContainsString(
            'page=1',
            $query->toUrl()
        );
        $this->assertStringContainsString(
            'page=99',
            $query->page(99)->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchLinksIsNotInitiallySet(Query $query) : void
    {
        $this->assertStringNotContainsString(
            'fetchLinks',
            $query->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchIsNotInitiallySet(Query $query) : void
    {
        $this->assertStringNotContainsString(
            'fetch',
            $query->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchCanBeProvidedWithStringArguments(Query $query) : void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetch('first', 'second')->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchCanBeProvidedWithIterableArgument(Query $query) : void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetch(...['first', 'second'])->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatFetchLinksCanBeProvidedWithIterableArgument(Query $query) : void
    {
        $this->assertStringContainsString(
            urlencode('first,second'),
            $query->fetchLinks(...['first', 'second'])->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatCallingFetchWithoutArgumentsRemovesParameterFromUrl(Query $query) : void
    {
        $this->assertStringNotContainsString(
            'fetch=',
            $query->fetch('first', 'second')->fetch()->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatCallingFetchLinksWithoutArgumentsRemovesParameterFromUrl(Query $query) : void
    {
        $this->assertStringNotContainsString(
            'fetchLinks=',
            $query->fetchLinks('first', 'second')->fetchLinks()->toUrl()
        );
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testQueriesForFormsWithADefaultQueryWillContainQueryInUrl(Query $query) : void
    {
        $this->assertStringContainsString('q=', $query->toUrl());
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testThatQueriesAreAppendedToDefaultQuery(Query $query) : void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);

        $query = $query->query($predicate);

        $this->assertStringContainsString($expect, $query->toUrl());
        $this->assertSame(2, substr_count($query->toUrl(), 'q='));
    }

    /** @dataProvider queryProvider */
    public function testThatSettingTheQueryWillAlterTheUrl(Query $query) : void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);
        $this->assertStringContainsString($expect, $query->query($predicate)->toUrl());
    }

    /** @dataProvider queryWithoutDefaultQueryProvider */
    public function testThatSettingEmptyPredicatesWillRemoveExistingQuery(Query $query) : void
    {
        $predicate = Predicate::at('document.id', 'baz');
        $expect = urlencode((string) $predicate);
        $query = $query->query($predicate)->query();
        $this->assertStringNotContainsString($expect, $query->toUrl());
    }

    /** @dataProvider queryWithDefaultQueryProvider */
    public function testThatSettingEmptyPredicatesDoesNotRemoveDefaultQuery(Query $query) : void
    {
        $query = $query->query();
        $this->assertStringContainsString('q=', $query->toUrl());
    }

    /** @dataProvider queryProvider */
    public function testThatOrderIsImplodedWithSquareBrackets(Query $query) : void
    {
        $expect = urlencode('[a,b,c]');
        $this->assertStringContainsString(
            $expect,
            $query->order('a', 'b', 'c')->toUrl()
        );
    }

    /** @dataProvider queryProvider */
    public function testThatOrderCanBeRemoved(Query $query) : void
    {
        $expect = urlencode('[a,b,c]');
        $this->assertStringNotContainsString(
            $expect,
            $query->order('a', 'b', 'c')->order()->toUrl()
        );
    }
}
