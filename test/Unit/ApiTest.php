<?php
declare(strict_types=1);

namespace PrismicTest;

use Http\Client\Curl\Client;
use Http\Mock\Client as MockClient;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\StreamFactory;
use Prismic\Api;
use Prismic\Exception\RequestFailure;
use PrismicTest\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use function sprintf;
use function uniqid;
use const CURLOPT_TIMEOUT_MS;

class ApiTest extends TestCase
{
    /** @var MockClient */
    private $httpClient;

    /** @var JsonResponse */
    private $response;

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

    public function testThatASuccessfulResponseWillYieldExpectedApiData() : void
    {
        $this->httpClient->setDefaultResponse($this->response);
        $api = Api::get('https://example.com', null, $this->httpClient);
        $data = $api->data();
        $this->assertContains('goats', $data->tags());
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
}
