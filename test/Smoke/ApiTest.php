<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Generator;
use Prismic\Api;
use Prismic\Exception\RequestFailure;
use Prismic\Predicate;
use Prismic\Value\Ref;

use function assert;
use function count;
use function sprintf;

/** @psalm-suppress DeprecatedMethod */
class ApiTest extends TestCase
{
    /**
     * @return Generator<string, array{0: Api, 1: string}>
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public static function documentIdDataProvider(): Generator
    {
        foreach (self::apiInstances() as $api) {
            assert($api instanceof Api);
            $response = $api->query(
                $api->createQuery()
                    ->resultsPerPage(10),
            );
            foreach ($response as $document) {
                yield sprintf('%s: %s', $api->host(), $document->id()) => [$api, $document->id()];
            }
        }
    }

    /** @dataProvider documentIdDataProvider */
    public function testThatFindByIdReturnsTheExpectedDocument(Api $api, string $id): void
    {
        $document = $api->findById($id);
        $this->assertNotNull($document);
        $this->assertSame($id, $document->id());
    }

    /**
     * @return Generator<string, array{0: Api, 1: string, 2: string}>
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public static function documentUidDataProvider(): Generator
    {
        foreach (self::apiInstances() as $api) {
            assert($api instanceof Api);
            foreach ($api->data()->types() as $type) {
                $response = $api->query(
                    $api->createQuery()
                        ->resultsPerPage(1)
                        ->query(Predicate::has(sprintf('my.%s.uid', $type->id()))),
                );

                foreach ($response as $document) {
                    $uid = $document->uid();
                    assert($uid !== null);

                    yield sprintf('%s: %s(%s)', $api->host(), $document->type(), $document->id()) => [
                        $api,
                        $document->type(),
                        $uid,
                    ];
                }
            }
        }
    }

    /** @dataProvider documentUidDataProvider */
    public function testThatFindByUidReturnsTheExpectedDocument(Api $api, string $type, string $uid): void
    {
        $document = $api->findByUid($type, $uid);
        $this->assertNotNull($document);
        $this->assertSame($uid, $document->uid());
        $this->assertSame($type, $document->type());
    }

    /**
     * @return Generator<string, array{0: Api, 1: string}>
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public static function bookmarkDataProvider(): Generator
    {
        foreach (self::apiInstances() as $api) {
            assert($api instanceof Api);
            foreach ($api->data()->bookmarks() as $bookmark) {
                yield sprintf('%s: %s', $api->host(), $bookmark->name()) => [$api, $bookmark->name()];
            }
        }
    }

    /** @dataProvider bookmarkDataProvider */
    public function testThatAllKnownBookmarksCanBeRetrieved(Api $api, string $bookmark): void
    {
        /**
         * There's not much to test here. The ID referenced by a bookmark may not resolve to a document, if
         * that document has since been deleted, or the linked document is unpublished.
         */
        self::assertNotNull($api->findByBookmark($bookmark));
    }

    /** @dataProvider apiDataProvider */
    public function testThatNextAndPreviousReturnTheExpectedResults(Api $api): void
    {
        $query = $api->createQuery()
            ->resultsPerPage(1);

        $first = $api->query($query);
        $this->assertNull($first->previousPage(), 'The first page of a result set should not have a previous page');
        $this->assertNull($api->previous($first), 'Calling previous with the Api on the first page should yield null');

        if (! count($first->results()) || $first->totalResults() < 2) {
            $this->markTestSkipped('Not enough documents in this repository to test.');
        }

        $second = $api->next($first);
        self::assertNotNull($second);
        $this->assertNotNull($second->previousPage());
        $firstAgain = $api->previous($second);
        $this->assertNotNull($firstAgain);

        $this->assertSame($first->nextPage(), $firstAgain->nextPage());
    }

    /** @dataProvider apiDataProvider */
    public function testThatSettingAnUnknownRefWillCauseAnException(Api $api): void
    {
        $query = $api->createQuery()
            ->resultsPerPage(1)
            ->ref(Ref::new('SomeId', 'unknownRef', 'Some Label', false));

        $this->expectException(RequestFailure::class);
        $api->query($query);
    }
}
