<?php
declare(strict_types=1);

namespace PrismicTest;

use Http\Client\Curl\Client;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockClient;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\StreamFactory;
use Prismic\Api;
use Prismic\Exception\AuthenticationError;
use Prismic\Exception\InvalidPreviewToken;
use Prismic\Exception\PrismicError;
use Prismic\Exception\RequestFailure;
use Prismic\Json;
use PrismicTest\Framework\CacheKeyInvalid;
use PrismicTest\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as InvalidCacheKeyError;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;
use function uniqid;
use function urlencode;

use const CURLOPT_TIMEOUT_MS;

class ApiTest extends TestCase
{
    /** @var MockClient */
    private $httpClient;

    /** @var JsonResponse */
    private $response;

    private const DOCUMENT_PREVIEW_PAYLOAD = <<<JSON
        {
          "label": "Example Label",
          "ref": "preview-ref",
          "mainDocument": "target-document-id",
          "type": "DRAFT"
        }
        JSON;

    private const NO_DOCUMENT_PREVIEW_PAYLOAD = <<<JSON
        {
          "label": "No Document",
          "ref": "preview-ref",
          "mainDocument": null,
          "type": "RELEASE"
        }
        JSON;

    protected function setUp() : void
    {
        parent::setUp();
        $this->httpClient = new MockClient();
        $response = new JsonResponse([]);
        $responseBody = (new StreamFactory())->createStream($this->jsonFixtureByFileName('api-data.json'));
        $this->response = $response->withBody($responseBody);
    }

    public function testThatAnUnreachableDomainNameWillCauseARequestFailure() : void
    {
        $unreachable = sprintf('https://%s.example.com/not-found', uniqid('', false));
        $client = new Client(null, null, [CURLOPT_TIMEOUT_MS => 500]);
        $api = Api::get($unreachable, null, $client);
        try {
            $api->data();
            $this->fail('Exception not thrown');
        } catch (RequestFailure $error) {
            $this->assertInstanceOf(ClientExceptionInterface::class, $error->getPrevious());
        }
    }

    public function testThatANetworkTimeOutWillCauseARequestFailure() : void
    {
        $reachable = 'https://www.google.com';
        $client = new Client(null, null, [CURLOPT_TIMEOUT_MS => 1]);
        $api = Api::get($reachable, null, $client);
        try {
            $api->data();
            $this->fail('Exception not thrown');
        } catch (RequestFailure $error) {
            $this->assertInstanceOf(ClientExceptionInterface::class, $error->getPrevious());
        }
    }

    public function testThtARedirectIsExceptional() : void
    {
        $this->httpClient->setDefaultResponse(new RedirectResponse('http://other.example.com'));
        $api = Api::get('http://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage('The request to the URL "http://example.com" resulted in a 302 redirect');
        $this->expectExceptionCode(302);
        $api->data();
    }

    public function testThatA400ClassErrorIsExceptional() : void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Nice Body', 400));
        $api = Api::get('http://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage('Error 400. The request to the URL "http://example.com" was rejected by the api. The error response body was "Nice Body"');
        $this->expectExceptionCode(400);
        $api->data();
    }

    /** @return int[][] */
    public function authErrorStatusCodes() : iterable
    {
        yield 401 => [401];

        yield 403 => [403];
    }

    /** @dataProvider authErrorStatusCodes */
    public function testThatA401WillCauseAnAuthenticationException(int $code) : void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Bad Auth', $code));
        $api = Api::get('http://example.com', null, $this->httpClient);
        $this->expectException(AuthenticationError::class);
        $this->expectExceptionMessage('Authentication failed for the api host "example.com"');
        $this->expectExceptionCode($code);
        $api->data();
    }

    public function testThatAServerErrorWillCauseAnException() : void
    {
        $this->httpClient->setDefaultResponse(new TextResponse('Whoops', 500));
        $api = Api::get('http://example.com', null, $this->httpClient);
        $this->expectException(RequestFailure::class);
        $this->expectExceptionMessage('The request to the URL "http://example.com" resulted in a server error. The error response body was "Whoops"');
        $this->expectExceptionCode(500);
        $api->data();
    }

    public function testThatTheHostnameOfTheRepoCanBeRetrieved() : void
    {
        $api = Api::get('https://foo.example.com/api/v2');
        $this->assertSame('foo.example.com', $api->host());
    }

    public function testThatASuccessfulResponseWillYieldExpectedApiData() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $data = $api->data();
        $this->assertContainsEquals('goats', $data->tags());
    }

    public function testRepeatedCallsToRetrieveApiDataReturnTheSameInstance() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $data = $api->data();
        $this->assertSame($data, $api->data());
    }

    public function testThatAccessTokenIsOmittedFromRequestWhenNull() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $api->data();
        $request = $this->httpClient->getLastRequest();
        $this->assertStringNotContainsString('access_token', $request->getUri()->getQuery());
    }

    public function testThatAccessTokenIsIncludedInRequestWhenNotNull() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', 'foo', $this->httpClient);
        $api->data();
        $request = $this->httpClient->getLastRequest();
        $this->assertStringContainsString('access_token=foo', $request->getUri()->getQuery());
    }

    public function testThatTheMasterRefIsReturnedByDefault() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->assertSame('master-ref', (string) $api->ref());
    }

    public function testThatPreviewIsNotActiveByDefault() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->assertFalse($api->inPreview());
    }

    /** @return mixed[] */
    public function cookiePayloads() : iterable
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
        ];
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatAPreviewRefIsReturnedWhenRequestCookiesArePresent(array $cookiePayload, string $expectedRef) : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $api->setRequestCookies($cookiePayload);
        $this->assertSame($expectedRef, (string) $api->ref());
        $this->assertTrue($api->inPreview());
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatCookieSuperGlobalsAreNotConsideredAfterConstruction(array $cookiePayload) : void
    {
        $backup = $_COOKIE;
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $_COOKIE = $cookiePayload;
        $this->assertFalse($api->inPreview());
        $_COOKIE = $backup;
    }

    /**
     * @param string[] $cookiePayload
     *
     * @dataProvider cookiePayloads
     */
    public function testThatCookieSuperGlobalsAreConsultedDuringConstruction(array $cookiePayload) : void
    {
        $backup = $_COOKIE;
        $_COOKIE = $cookiePayload;
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->assertTrue($api->inPreview());
        $_COOKIE = $backup;
    }

    public function testThatPreviewSessionWillReturnNullWhenNoDocumentIsGiven() : void
    {
        $api = Api::get('https://example.com', null, $this->httpClient);
        $this->httpClient->setDefaultResponse(new JsonResponse(Json::decodeObject(self::NO_DOCUMENT_PREVIEW_PAYLOAD)));
        $this->assertNull(
            $api->previewSession('https://example.com/previews/stuff:morestuff')
        );
    }

    public function testThatADocumentLinkWillBeReturnedWhenAPreviewResponsePointsToAKnownDocument() : void
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
        $this->httpClient->on($matcher, function (RequestInterface $request) use ($documentResponse) : ResponseInterface {
            $url = $request->getUri();
            $this->assertStringContainsString('target-document-id', $url->getQuery());

            return $documentResponse;
        });

        $link = $api->previewSession('https://example.com/get-preview');

        $this->assertNotNull($link);
        $this->assertSame('DOC_ID', $link->id());
        $this->assertSame('DOC_UID', $link->uid());
        $this->assertSame('doc', $link->type());
        $this->assertSame('en-gb', $link->language());
    }

    public function testThatAnExceptionIsThrownWhenThePreviewTokenDoesNotMatchTheRepositoryHostname() : void
    {
        $api = Api::get('https://example.com/api/v2', null, $this->httpClient);
        $this->expectException(InvalidPreviewToken::class);
        $this->expectExceptionMessage('The preview url has been rejected because its host name "foobar.com" does not match the api host "example.com"');
        $api->previewSession('https://foobar.com/something-nefarious');
    }

    public function testThatAnExceptionIsThrownWhenThePreviewTokenIsAnInvalidUrl() : void
    {
        $api = Api::get('https://example.com/api/v2', null, $this->httpClient);
        $this->expectException(InvalidPreviewToken::class);
        $this->expectExceptionMessage('The given preview token is not a valid url');
        $api->previewSession('whatsup:// this is not a url');
    }

    /** @return mixed[] */
    public function previewHostVariations() : iterable
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
    public function testThatAnExceptionIsNotThrownWithCdnVariationsOfApiHostNames(string $configuredHost, string $tokenHost) : void
    {
        $api = Api::get(sprintf('https://%s', $configuredHost), null, $this->httpClient);
        $previewResponse = new JsonResponse(Json::decodeObject(self::NO_DOCUMENT_PREVIEW_PAYLOAD));
        $matcher = new RequestMatcher('/get-preview');
        $sentRequest = null;
        $this->httpClient->on($matcher, static function (RequestInterface $request) use ($previewResponse, &$sentRequest) {
            $sentRequest = $request;

            return $previewResponse;
        });
        $this->assertNull(
            $api->previewSession(urlencode(
                sprintf('https://%s/get-preview', $tokenHost)
            ))
        );
        $this->assertInstanceOf(RequestInterface::class, $sentRequest);
    }

    public function testThatAnExceptionIsThrownWhenAnHttpClientCannotBeDiscovered() : void
    {
        $strategies = Psr18ClientDiscovery::getStrategies();
        Psr18ClientDiscovery::setStrategies([]);
        try {
            Api::get('foo');
            $this->fail('An exception was not thrown');
        } catch (PrismicError $error) {
            $this->assertStringContainsString('An HTTP client cannot be determined', $error->getMessage());
            $this->assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr18ClientDiscovery::setStrategies($strategies);
        }
    }

    public function testThatAnExceptionIsThrownWhenARequestFactoryCannotBeDiscovered() : void
    {
        $strategies = Psr17FactoryDiscovery::getStrategies();
        Psr17FactoryDiscovery::setStrategies([]);
        try {
            Api::get('foo', null, $this->createMock(ClientInterface::class));
            $this->fail('An exception was not thrown');
        } catch (PrismicError $error) {
            $this->assertStringContainsString('A request factory cannot be determined', $error->getMessage());
            $this->assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr17FactoryDiscovery::setStrategies($strategies);
        }
    }

    public function testThatAnExceptionIsThrownWhenAnUriFactoryCannotBeDiscovered() : void
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
            $this->fail('An exception was not thrown');
        } catch (PrismicError $error) {
            $this->assertStringContainsString('A URI factory cannot be determined', $error->getMessage());
            $this->assertInstanceOf(NotFoundException::class, $error->getPrevious());

            return;
        } finally {
            Psr17FactoryDiscovery::setStrategies($strategies);
        }
    }

    public function testThatExceptionsThrownDueToInvalidCacheKeysAreWrapped() : void
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
}
