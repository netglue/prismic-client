<?php
declare(strict_types=1);

namespace PrismicSmokeTest;

use Prismic\Api;
use Prismic\Predicate;
use function sprintf;

class ApiTest extends TestCase
{
    /** @return mixed[] */
    public function documentIdDataProvider() : iterable
    {
        foreach ($this->apiInstances() as $api) {
            $response = $api->query(
                $api->createQuery()
                    ->resultsPerPage(10)
            );
            foreach ($response as $document) {
                yield sprintf('%s: %s', $api->host(), $document->id()) => [$api, $document->id()];
            }
        }
    }

    /** @dataProvider documentIdDataProvider */
    public function testThatFindByIdReturnsTheExpectedDocument(Api $api, string $id) : void
    {
        $document = $api->findById($id);
        $this->assertNotNull($document);
        $this->assertSame($id, $document->id());
    }

    /** @return mixed[] */
    public function documentUidDataProvider() : iterable
    {
        foreach ($this->apiInstances() as $api) {
            foreach ($api->data()->types() as $type) {
                $response = $api->query(
                    $api->createQuery()
                        ->resultsPerPage(1)
                        ->query(Predicate::has(sprintf('my.%s.uid', $type->id())))
                );

                foreach ($response as $document) {
                    yield sprintf('%s: %s(%s)', $api->host(), $document->type(), $document->id()) => [
                        $api,
                        $document->type(),
                        $document->uid(),
                    ];
                }
            }
        }
    }

    /** @dataProvider documentUidDataProvider */
    public function testThatFindByUidReturnsTheExpectedDocument(Api $api, string $type, string $uid) : void
    {
        $document = $api->findByUid($type, $uid);
        $this->assertNotNull($document);
        $this->assertSame($uid, $document->uid());
        $this->assertSame($type, $document->type());
    }

    /** @return mixed[] */
    public function bookmarkDataProvider() : iterable
    {
        foreach ($this->apiInstances() as $api) {
            foreach ($api->data()->bookmarks() as $bookmark) {
                yield sprintf('%s: %s', $api->host(), $bookmark->name()) => [$api, $bookmark->name()];
            }
        }
    }

    /** @dataProvider bookmarkDataProvider */
    public function testThatAllKnownBookmarksCanBeRetrieved(Api $api, string $bookmark) : void
    {
        /**
         * There's not much to test here. The ID referenced by a bookmark may not resolve to a document, if
         * that document has since been deleted, or the linked document is unpublished.
         */
        $api->findByBookmark($bookmark);
        $this->addToAssertionCount(1);
    }
}
