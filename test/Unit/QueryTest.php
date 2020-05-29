<?php
declare(strict_types=1);

namespace PrismicTest;

use Prismic\Json;
use Prismic\Query;
use Prismic\Value\FormSpec;
use PrismicTest\Framework\TestCase;
use function sprintf;
use function urlencode;

class QueryTest extends TestCase
{
    /** @return mixed[] */
    public function queryProvider() : iterable
    {
        $forms = Json::decodeObject($this->jsonFixtureByFileName('forms.json'));

        return [
            'Standard Form' => [
                new Query(FormSpec::factory('everything', $forms->everything)),
            ],
            'Collection' => [
                new Query(FormSpec::factory('collection', $forms->collection)),
            ],
            'With Query' => [
                new Query(FormSpec::factory('withQuery', $forms->withQuery)),
            ],
        ];
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
}
