<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Generator;
use Prismic\Api;
use Prismic\ApiClient;
use Prismic\Predicate;

use function sha1;
use function sprintf;
use function uniqid;

class CacheTest extends TestCase
{
    /** @return Generator<string, array{0:Api}> */
    public static function cachingApiClientProvider(): Generator
    {
        foreach (self::compileEndPoints() as $uri => $token) {
            $api = Api::get($uri, $token, null, null, null, null, self::psrCachePool());

            yield $api->host() => [$api];
        }
    }

    /** @dataProvider cachingApiClientProvider */
    public function testThatAGetRequestWillResultInAnExpectedKeyBeingPresentInTheCache(ApiClient $api): void
    {
        $cache = self::psrCachePool();

        $query = $api->createQuery()
            ->resultsPerPage(1)
            ->query(Predicate::fulltext('document', uniqid('', false)));

        $expectedKey = sha1(sprintf('GET %s', $query->toUrl()));

        self::assertFalse($cache->hasItem($expectedKey));
        $api->query($query);
        self::assertTrue($cache->hasItem($expectedKey));
    }

    /** @dataProvider cachingApiClientProvider */
    public function testThatRepeatedQueriesHitTheCache(ApiClient $api): void
    {
        $cache = self::psrCachePool();

        $query = $api->createQuery()
            ->resultsPerPage(1)
            ->query(Predicate::fulltext('document', uniqid('', false)));

        $expectedKey = sha1(sprintf('GET %s', $query->toUrl()));

        $item = $cache->getItem($expectedKey);
        self::assertFalse($item->isHit());

        $result = $api->query($query);

        $item = $cache->getItem($expectedKey);
        self::assertTrue($item->isHit());

        self::assertEquals($result, $api->query($query));
    }
}
