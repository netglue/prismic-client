<?php
declare(strict_types=1);

namespace Prismic;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Prismic\Exception\RequestFailure;
use Prismic\Value\ApiData;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use function http_build_query;
use function parse_str;

final class Api
{
    /** @var ClientInterface */
    private $httpClient;
    /** @var UriInterface */
    private $baseUri;
    /** @var ApiData */
    private $data;
    /** @var RequestFactoryInterface */
    private $requestFactory;
    /** @var string|null */
    private $accessToken;

    private function __construct(
        UriInterface $uri,
        ClientInterface $httpClient,
        ?string $accessToken,
        RequestFactoryInterface $requestFactory
    ) {
        $this->baseUri = $uri;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->accessToken = $accessToken;
    }

    public static function get(
        string $apiBaseUri,
        ?string $accessToken = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null
    ) : self {
        $uriFactory = Psr17FactoryDiscovery::findUrlFactory();
        $accessToken = (string) $accessToken === '' ? null : $accessToken;

        return new self(
            $uriFactory->createUri($apiBaseUri),
            $httpClient ?? Psr18ClientDiscovery::find(),
            $accessToken,
            $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory()
        );
    }

    public function data() : ApiData
    {
        if ($this->data) {
            return $this->data;
        }

        $uri = $this->accessToken
            ? $this->uriWithQueryValue($this->baseUri, 'access_token', $this->accessToken)
            : $this->baseUri;

        $response = $this->sendRequest($uri);
        $this->data = ApiData::factory(Json::decodeObject((string) $response->getBody()));

        return $this->data;
    }

    private function sendRequest(UriInterface $uri, string $method = 'GET') : ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw RequestFailure::withClientException($clientException);
        }
    }

    /** @param mixed $value */
    private function uriWithQueryValue(UriInterface $uri, string $parameter, $value) : UriInterface
    {
        $params = [];
        parse_str((string) $uri, $params);
        $params[$parameter] = $value;

        return $uri->withQuery(http_build_query($params));
    }
}
