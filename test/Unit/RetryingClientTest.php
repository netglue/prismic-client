<?php

declare(strict_types=1);

namespace PrismicTest;

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Prismic\ApiClient;
use Prismic\Exception\RequestFailure;
use Prismic\Json;
use Prismic\Query;
use Prismic\ResultSet;
use Prismic\ResultSet\StandardResultSet;
use Prismic\RetryingClient;
use Prismic\Value\ApiData;
use Prismic\Value\Ref;
use PrismicTest\Framework\TestCase;
use Throwable;

/**
 * @psalm-suppress DeprecatedMethod
 */
class RetryingClientTest extends TestCase
{
    /** @var RetryingClient */
    private $client;
    /** @var MockObject&ApiClient */
    private $wrappedClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wrappedClient = $this->createMock(ApiClient::class);
        $this->client = RetryingClient::wrap($this->wrappedClient);
    }

    public function testThatTheLastExceptionIsInitiallyNull(): void
    {
        self::assertNull($this->client->lastRequestFailure());
    }

    private function emptyResultSet(): StandardResultSet
    {
        return StandardResultSet::factory(Json::decodeObject($this->jsonFixtureByFileName('empty-result-set.json')));
    }

    public function testThatTheLastExceptionWillBeNullWhenTheQueryExecutesWithoutAnError(): void
    {
        $query = $this->createQuery();
        $results = $this->emptyResultSet();

        $this->wrappedClient->expects(self::once())
            ->method('query')
            ->with(self::identicalTo($query))
            ->willReturn($results);

        self::assertSame($results, $this->client->query($query));
        self::assertNull($this->client->lastRequestFailure());
    }

    public function testThatErrorsAreStillThrownWhenTheUsedRefIsTheMaster(): void
    {
        $master = Ref::new('foo', 'foo', 'foo', true);
        $query = $this->createQuery();
        $exception = RequestFailure::withClientError(new ServerRequest(), new TextResponse('[]'));

        $this->wrappedClient->expects(self::once())
            ->method('ref')
            ->willReturn($master);

        $this->wrappedClient->expects(self::once())
            ->method('query')
            ->with(self::identicalTo($query))
            ->willThrowException($exception);

        try {
            $this->client->query($query);
            $this->fail('An exception was not thrown');
        } catch (Throwable $error) {
            self::assertSame($exception, $error);
        }
    }

    public function testThatWhenARequestFailureOccursTheQueryIsRetriedWithTheMasterRef(): void
    {
        $results = $this->emptyResultSet();
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data.json')));
        $usedRef = Ref::new('not-master', 'foo', 'foo', false);
        $query = $this->createQuery()->ref($usedRef);
        $exception = RequestFailure::withClientError(new ServerRequest(), new TextResponse('[]'));
        $matcher = self::exactly(2);

        $this->wrappedClient->expects(self::once())
            ->method('ref')
            ->willReturn($usedRef);

        $this->wrappedClient->expects(self::once())
            ->method('data')
            ->willReturn($data);

        $this->wrappedClient->expects($matcher)
            ->method('query')
            ->with(self::isInstanceOf(Query::class))
            ->willReturnCallback(static function (Query $queryInput) use ($data, $results, $exception, $query, $matcher): ResultSet {
                /** @psalm-suppress InternalMethod */
                if ($matcher->getInvocationCount() === 1) {
                    throw $exception;
                }

                self::assertNotSame($query, $queryInput, 'The query should be modified on the second invocation and was not');
                self::assertStringContainsString($data->master()->ref(), $queryInput->toUrl(), 'The query for the second invocation should use the master ref');

                return $results;
            });

        self::assertSame($results, $this->client->query($query));
        self::assertSame($exception, $this->client->lastRequestFailure());
    }

    public function testThatHostMethodProxiesToWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('host')
            ->willReturn('goats');

        self::assertEquals('goats', $this->client->host());
    }

    public function testThatDataMethodProxiesToWrappedClient(): void
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data.json')));

        $this->wrappedClient->expects(self::once())
            ->method('data')
            ->willReturn($data);

        self::assertSame($data, $this->client->data());
    }

    public function testThatRefMethodProxiesToWrappedClient(): void
    {
        $ref = Ref::new('whatever', 'whatever', 'whatever', false);
        $this->wrappedClient->expects(self::once())
            ->method('ref')
            ->willReturn($ref);

        self::assertSame($ref, $this->client->ref());
    }

    private function createQuery(): Query
    {
        $data = ApiData::factory(Json::decodeObject($this->jsonFixtureByFileName('api-data.json')));
        $formName = 'some-collection';
        $form = $data->form($formName);

        return new Query($form);
    }

    public function testThatCreateQueryMethodProxiesToWrappedClient(): void
    {
        $query = $this->createQuery();

        $this->wrappedClient->expects(self::once())
            ->method('createQuery')
            ->with('some-collection')
            ->willReturn($query);

        self::assertSame($query, $this->client->createQuery('some-collection'));
    }

    public function testThatQueryFirstProxiesToWrappedClient(): void
    {
        $query = $this->createQuery();

        $this->wrappedClient->expects(self::once())
            ->method('queryFirst')
            ->with(self::identicalTo($query))
            ->willReturn(null);

        self::assertNull($this->client->queryFirst($query));
    }

    public function testThatFindByIdProxiesToTheWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('findById')
            ->with(self::equalTo('goats'))
            ->willReturn(null);

        self::assertNull($this->client->findById('goats'));
    }

    public function testThatFindByUidProxiesToTheWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('findByUid')
            ->with(
                self::equalTo('type'),
                self::equalTo('uid'),
                self::equalTo('lang')
            )
            ->willReturn(null);

        self::assertNull($this->client->findByUid('type', 'uid', 'lang'));
    }

    public function testThatCookiesAreProvidedToWrappedClient(): void
    {
        $cookies = ['foo' => 'bar'];
        $this->wrappedClient->expects(self::once())
            ->method('setRequestCookies')
            ->with(self::equalTo($cookies));

        $this->client->setRequestCookies($cookies);
    }

    public function testThatInPreviewProxiesToWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('inPreview')
            ->willReturn(true);

        self::assertTrue($this->client->inPreview());
    }

    public function testThatPreviewSessionProxiesToWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('previewSession')
            ->with('foo-bar')
            ->willReturn(null);

        self::assertNull($this->client->previewSession('foo-bar'));
    }

    public function testThatFindByBookmarkProxiesToWrappedClient(): void
    {
        $this->wrappedClient->expects(self::once())
            ->method('findByBookmark')
            ->with('foo-bar')
            ->willReturn(null);

        self::assertNull($this->client->findByBookmark('foo-bar'));
    }

    public function testThatFindAllProxiesToWrappedClient(): void
    {
        $resultSet = $this->emptyResultSet();
        $query = $this->createQuery();

        $this->wrappedClient->expects(self::once())
            ->method('findAll')
            ->with(self::identicalTo($query))
            ->willReturn($resultSet);

        self::assertSame($resultSet, $this->client->findAll($query));
    }

    public function testThatNextProxiesToWrappedClient(): void
    {
        $resultSet = $this->emptyResultSet();
        $this->wrappedClient->expects(self::once())
            ->method('next')
            ->with(self::identicalTo($resultSet))
            ->willReturn(null);

        self::assertNull($this->client->next($resultSet));
    }

    public function testThatPreviousProxiesToWrappedClient(): void
    {
        $resultSet = $this->emptyResultSet();
        $this->wrappedClient->expects(self::once())
            ->method('previous')
            ->with(self::identicalTo($resultSet))
            ->willReturn(null);

        self::assertNull($this->client->previous($resultSet));
    }
}
