<?php

declare(strict_types=1);

namespace PrismicTest;

use Generator;
use Http\Client\Curl\Client;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockClient;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use Prismic\Api;
use Prismic\Exception\AuthenticationError;
use Prismic\Exception\InvalidPreviewToken;
use Prismic\Exception\PrismicError;
use Prismic\Exception\RequestFailure;
use Prismic\Json;
use Prismic\ResultSet\StandardResultSetFactory;
use PrismicTest\Framework\CacheKeyInvalid;
use PrismicTest\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function parse_str;
use function sprintf;
use function uniqid;
use function urlencode;

use const CURLOPT_TIMEOUT_MS;

class ApiTest extends TestCase
{
    private MockClient $httpClient;

    private JsonResponse $response;

    private const DOCUMENT_PREVIEW_PAYLOAD = <<<'JSON'
        {
          "label": "Example Label",
          "ref": "preview-ref",
          "mainDocument": "target-document-id",
          "type": "DRAFT"
        }
        JSON;

    private const NO_DOCUMENT_PREVIEW_PAYLOAD = <<<'JSON'
        {
          "label": "No Document",
          "ref": "preview-ref",
          "mainDocument": null,
          "type": "RELEASE"
        }
        JSON;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new MockClient();
        $response = new JsonResponse([]);
        $responseBody = (new StreamFactory())->createStream($this->jsonFixtureByFileName('api-data.json'));
        $this->response = $response->withBody($responseBody);
    }

    public function testThatAnUnreachableDomainNameWillCauseARequestFailure(): void
    {
        $unreachable = sprintf('https://%s.example.com/not-found', uniqid('', false));
        $client = new Client(null, null, [CURLOPT_TIMEOUT_MS => 500]);
        $api = Api::get($unreachable, null, $client);
        try {
            $api->data();
            self::fail('Exception not thrown');
        } catch (RequestFailure $error) {
            self::assertInstanceOf(ClientExceptionInterface::class, $error->getPrevious());
        }
    }

    public function testThatANetworkTimeOutWillCauseARequestFailure(): void
    {
        $reachable = 'https://www.google.com';
        $client = new Client(null, null, [CURLOPT_TIMEOUT_MS => 1]);
        $api = Api::get($reachable, null, $client);
        try {
            $api->data();
            self::fail('Exception not thrown');
        } catch (RequestFailure $error) {
            self::assertInstanceOf(ClientExceptionInterface::class, $error->getPrevious());
        }
    }

    public function testThtARedirectIsExceptional(): void
    {
        $this->httpClient->setDefaultResponse(new RedirectResponse('https://other.example.com'));
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage('The request to the URL "https://example.com" resulted in a 302 redirect');
        $this->expectExceptionCode(302);
        $api->data();
    }

    public function testThatA400ClassErrorIsExceptional(): void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Nice Body', 400));
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage(
            'Error 400. The request to the URL "https://example.com" was rejected '
            . 'by the api. The error response body was "Nice Body"',
        );
        $this->expectExceptionCode(400);
        $api->data();
    }

    /** @return Generator<int, int[]> */
    public static function authErrorStatusCodes(): Generator
    {
        yield 401 => [401];

        yield 403 => [403];
    }

    /** @dataProvider authErrorStatusCodes */
    public function testThatA401WillCauseAnAuthenticationException(int $code): void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Bad Auth', $code));
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->expectException(AuthenticationError::class);
        $this->expectExceptionMessage('Authentication failed for the api host "example.com"');
        $this->expectExceptionCode($code);
        $api->data();
    }

    public function testThatAServerErrorWillCauseAnException(): void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Whoops', 500));
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage(
            'The request to the URL "https://example.com" resulted in a server error. '
            . 'The error response body was "Whoops"',
        );
        $this->expectExceptionCode(500);
        $api->data();
    }

    public function testThatTheHostnameOfTheRepoCanBeRetrieved(): void
    {
        $api = Api::get('https://foo.example.com/api/v2');
        self::assertSame('foo.example.com', $api->host());
    }

    public function testThatASuccessfulResponseWillYieldExpectedApiData(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $data = $api->data();
        /** @psalm-suppress DeprecatedMethod */
        self::assertContainsEquals('goats', $data->tags());
    }

    public function testRepeatedCallsToRetrieveApiDataReturnTheSameInstance(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $data = $api->data();
        self::assertSame($data, $api->data());
    }

    public function testThatAccessTokenIsOmittedFromRequestWhenNull(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $api->data();
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertStringNotContainsString('access_token', $request->getUri()->getQuery());
    }

    public function testThatAccessTokenIsIncludedInRequestWhenNotNull(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', 'foo', $this->httpClient);
        $api->data();
        $request = $this->httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertStringContainsString('access_token=foo', $request->getUri()->getQuery());
    }

    public function testThatTheMasterRefIsReturnedByDefault(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        self::assertSame('master-ref', (string) $api->ref());
    }

    public function testThatPreviewIsNotActiveByDefault(): void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        self::assertFalse($api->inPreview());
    }

    /** @return array<string, array{0: string[], 1: string}> */
    public static function cookiePayloads(): array
    {
        return [
            'io.prismic.preview' => [
                ['io.prismic.preview' => 'a'],
                'a',
            ],
            'io_prismic_preview' => [
                ['io_prismic_preview' => 'b'],
                'b',
            ],
            'Json Payload looking like an actual preview cookie payload' => [
                ['io.prismic.preview' => '{"_tracker":"SomeRandomUUID","repo.prismic.io":{"preview":"https://repo.prismic.io/previews/SomeID:SomeID?websitePreviewId=SomeID"}}'],
                '{"_tracker":"SomeRandomUUID","repo.prismic.io":{"preview":"https://repo.prismic.io/previews/SomeID:SomeID?websitePreviewId=SomeID"}}',
            ],
        ];
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatAPreviewRefIsReturnedWhenRequestCookiesArePresent(
        array $cookiePayload,
        string $expectedRef,
    ): void {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $api->setRequestCookies($cookiePayload);
        self::assertSame($expectedRef, (string) $api->ref());
        self::assertTrue($api->inPreview());
    }

    public function testThatTheApiIsNotConsideredInPreviewModeWhenThePreviewCookieContainsOnlyATrackerId(): void
    {
        $cookiePayload = [Api::PREVIEW_COOKIE => '{"_tracker":"SomeRandomUUID"}'];
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $api->setRequestCookies($cookiePayload);
        $master = $api->data()->master();
        self::assertEquals($master, $api->ref());
        self::assertFalse($api->inPreview());
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatCookieSuperGlobalsAreNotConsideredAfterConstruction(array $cookiePayload): void
    {
        $backup = $_COOKIE;
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $_COOKIE = $cookiePayload;
        self::assertFalse($api->inPreview());
        $_COOKIE = $backup;
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatCookieSuperGlobalsAreConsultedDuringConstruction(array $cookiePayload): void
    {
        $backup = $_COOKIE;
        $_COOKIE = $cookiePayload;
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        self::assertTrue($api->inPreview());
        $_COOKIE = $backup;
    }

    public function testThatPreviewSessionWillReturnNullWhenNoDocumentIsGiven(): void
    {
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->httpClient->setDefaultResponse(new JsonResponse(Json::decodeObject(self::NO_DOCUMENT_PREVIEW_PAYLOAD)));
        self::assertNull(
            $api->previewSession('https://example.com/previews/stuff:morestuff'),
        );
    }

    public function testThatADocumentLinkWillBeReturnedWhenAPreviewResponsePointsToAKnownDocument(): void
    {
        $api = Api::get('https://example.com/api/v2', null, $this->httpClient);

        // Default Response will return the API Data payload
        $this->httpClient->setDefaultResponse($this->response);

        // First Response is the preview payload
        $previewResponse = new JsonResponse(Json::decodeObject(self::DOCUMENT_PREVIEW_PAYLOAD));
        $matcher = new RequestMatcher('get-preview');
        $this->httpClient->on($matcher, $previewResponse);

        // Second response should be a search result for a single document
        $documentResponse = new JsonResponse(Json::decodeObject($this->jsonFixtureByFileName('response.json')));
        $matcher = new RequestMatcher('/api/v2/documents/search');
        $this->httpClient->on(
            $matcher,
            static function (RequestInterface $request) use ($documentResponse): ResponseInterface {
                $url = $request->getUri();
                self::assertStringContainsString('target-document-id', $url->getQuery());

                return $documentResponse;
            },
        );

        $link = $api->previewSession('https://example.com/get-preview');

        self::assertNotNull($link);
        self::assertSame('DOC_ID', $link->id());
        self::assertSame('DOC_UID', $link->uid());
        self::assertSame('doc', $link->type());
        self::assertSame('en-gb', $link->language());
    }

    public function testThatAnExceptionIsThrownWhenThePreviewTokenDoesNotMatchTheRepositoryHostname(): void
    {
        $api = Api::get('https://example.com/api/v2', null, $this->httpClient);
        $this->expectException(InvalidPreviewToken::class);
        $this->expectExceptionMessage(
            'The preview url has been rejected because its host name '
            . '"foobar.com" does not match the api host "example.com"',
        );
        $api->previewSession('https://foobar.com/something-nefarious');
    }

    public function testThatAnExceptionIsThrownWhenThePreviewTokenIsAnInvalidUrl(): void
    {
        $api = Api::get('https://example.com/api/v2', null, $this->httpClient);
        $this->expectException(InvalidPreviewToken::class);
        $this->expectExceptionMessage('The given preview token is not a valid url');
        $api->previewSession('whatsup:// this is not a url');
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function previewHostVariations(): array
    {
        return [
            'CDN Configured, Not in Preview' => [
                'my.cdn.apihost.com',
                'my.apihost.com',
            ],
            'CDN not configured but present in preview token' => [
                'my.apihost.com',
                'my.cdn.apihost.com',
            ],
            'CDN configured in neither' => [
                'my.apihost.com',
                'my.apihost.com',
            ],
            'CDN in both' => [
                'my.cdn.apihost.com',
                'my.cdn.apihost.com',
            ],
        ];
    }

    /** @dataProvider previewHostVariations */
    public function testThatAnExceptionIsNotThrownWithCdnVariationsOfApiHostNames(
        string $configuredHost,
        string $tokenHost,
    ): void {
        $api = Api::get(sprintf('https://%s', $configuredHost), null, $this->httpClient);
        $previewResponse = new JsonResponse(Json::decodeObject(self::NO_DOCUMENT_PREVIEW_PAYLOAD));
        $matcher = new RequestMatcher('/get-preview');
        $sentRequest = null;
        $this->httpClient->on(
            $matcher,
            static function (RequestInterface $request) use ($previewResponse, &$sentRequest) {
                $sentRequest = $request;

                return $previewResponse;
            },
        );
        self::assertNull(
            $api->previewSession(urlencode(
                sprintf('https://%s/get-preview', $tokenHost),
            )),
        );
        self::assertInstanceOf(RequestInterface::class, $sentRequest);
    }

    public function testThatAnExceptionIsThrownWhenAnHttpClientCannotBeDiscovered(): void
    {
        $strategies = Psr18ClientDiscovery::getStrategies();
        Psr18ClientDiscovery::setStrategies([]);
        try {
            Api::get('foo');
            self::fail('An exception was not thrown');
        } catch (PrismicError $error) {
            self::assertStringContainsString('An HTTP client cannot be determined', $error->getMessage());
            self::assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr18ClientDiscovery::setStrategies($strategies);
        }
    }

    public function testThatAnExceptionIsThrownWhenARequestFactoryCannotBeDiscovered(): void
    {
        $strategies = Psr17FactoryDiscovery::getStrategies();
        Psr17FactoryDiscovery::setStrategies([]);
        try {
            Api::get('foo', null, $this->createMock(ClientInterface::class));
            self::fail('An exception was not thrown');
        } catch (PrismicError $error) {
            self::assertStringContainsString('A request factory cannot be determined', $error->getMessage());
            self::assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr17FactoryDiscovery::setStrategies($strategies);
        }
    }

    public function testThatAnExceptionIsThrownWhenAnUriFactoryCannotBeDiscovered(): void
    {
        $strategies = Psr17FactoryDiscovery::getStrategies();
        Psr17FactoryDiscovery::setStrategies([]);
        try {
            Api::get(
                'foo',
                null,
                $this->createMock(ClientInterface::class),
                $this->createMock(RequestFactoryInterface::class),
            );
            self::fail('An exception was not thrown');
        } catch (PrismicError $error) {
            self::assertStringContainsString('A URI factory cannot be determined', $error->getMessage());
            self::assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr17FactoryDiscovery::setStrategies($strategies);
        }
    }

    public function testThatExceptionsThrownDueToInvalidCacheKeysAreWrapped(): void
    {
        $cacheException = new CacheKeyInvalid();
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')
            ->willThrowException($cacheException);

        $api = Api::get('http://example.com', null, $this->httpClient, null, null, null, $cache);

        try {
            $api->data();
            static::fail('An exception was not thrown');
        } catch (PrismicError $error) {
            static::assertSame($cacheException, $error->getPrevious());
        }
    }

    public function testThatTheNextUrlInAPagedResultContainsTheExpectedParameters(): void
    {
        $httpClient = new MockClient();
        $response = new JsonResponse([]);
        $responseBody = (new StreamFactory())->createStream($this->jsonFixtureByFileName('empty-result-set.json'));
        $response = $response->withBody($responseBody);
        $httpClient->setDefaultResponse($response);

        $rsFactory = new StandardResultSetFactory();
        $initialResultSet = $rsFactory->withJsonObject(Json::decodeObject($this->jsonFixtureByFileName('paginated-result-set.json')));

        $client = Api::get(
            'https://example.com',
            'some-token',
            $httpClient,
            new RequestFactory(),
            new UriFactory(),
            $rsFactory,
            null,
        );

        $client->next($initialResultSet);

        $request = $httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        $uri = $request->getUri();
        self::assertInstanceOf(UriInterface::class, $uri);

        parse_str($uri->getQuery(), $sentParameters);
        self::assertArrayHasKey('ref', $sentParameters);
        self::assertEquals('expect-ref', $sentParameters['ref']);
        self::assertArrayHasKey('page', $sentParameters);
        self::assertEquals(3, $sentParameters['page']);
        self::assertArrayHasKey('access_token', $sentParameters);
        self::assertEquals('some-token', $sentParameters['access_token']);
    }

    public function testThatThePreviousUrlInAPagedResultContainsTheExpectedParameters(): void
    {
        $httpClient = new MockClient();
        $response = new JsonResponse([]);
        $responseBody = (new StreamFactory())->createStream($this->jsonFixtureByFileName('empty-result-set.json'));
        $response = $response->withBody($responseBody);
        $httpClient->setDefaultResponse($response);

        $rsFactory = new StandardResultSetFactory();
        $initialResultSet = $rsFactory->withJsonObject(Json::decodeObject($this->jsonFixtureByFileName('paginated-result-set.json')));

        $client = Api::get(
            'https://example.com',
            'some-token',
            $httpClient,
            new RequestFactory(),
            new UriFactory(),
            $rsFactory,
            null,
        );

        $client->previous($initialResultSet);

        $request = $httpClient->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $request);
        $uri = $request->getUri();
        self::assertInstanceOf(UriInterface::class, $uri);

        parse_str($uri->getQuery(), $sentParameters);
        self::assertArrayHasKey('ref', $sentParameters);
        self::assertEquals('expect-ref', $sentParameters['ref']);
        self::assertArrayHasKey('page', $sentParameters);
        self::assertEquals(1, $sentParameters['page']);
        self::assertArrayHasKey('access_token', $sentParameters);
        self::assertEquals('some-token', $sentParameters['access_token']);
    }
}
