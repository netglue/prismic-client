<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Prismic\Api;
use Prismic\ApiClient;
use Prismic\Predicate;

use function sha1;
use function sprintf;
use function uniqid;

class CacheTest extends TestCase
{
    /** @return Api[][] */
    public function cachingApiClientProvider(): iterable
    {
        foreach ($this->compileEndPoints() as $uri => $token) {
            $api = Api::get($uri, $token, null, null, null, null, $this->psrCachePool());

            yield $api->host() => [$api];
        }
    }

    /** @dataProvider cachingApiClientProvider */
    public function testThatRepeatedQueriesHitTheCache(ApiClient $api): void
    {
        $cache = $this->psrCachePool();

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
