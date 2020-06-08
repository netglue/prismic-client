<?php
declare(strict_types=1);

namespace Prismic;

use Http\Discovery\Exception as DiscoveryError;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Exception\InvalidArgument;
use Prismic\Exception\PrismicError;
use Prismic\Exception\RequestFailure;
use Prismic\Exception\RuntimeError;
use Prismic\ResultSet\ResultSetFactory;
use Prismic\ResultSet\StandardResultSetFactory;
use Prismic\Value\ApiData;
use Prismic\Value\Ref;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use function http_build_query;
use function parse_str;
use function sprintf;
use function str_replace;
use function urldecode;

final class Api implements ApiClient
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var UriInterface */
    private $baseUri;

    /** @var ApiData|null */
    private $data;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var string|null */
    private $accessToken;

    /** @var UriFactoryInterface */
    private $uriFactory;

    /**
     * Request cookies to inspect for preview or experiment refs
     *
     * By default, this array is populated with the $_COOKIE super global but can be overridden with setRequestCookies()
     *
     * @var string[]
     */
    private $requestCookies;

    /**
     * This factory is responsible for creating result sets from HTTP responses
     *
     * @var ResultSetFactory
     */
    private $resultSetFactory;

    private function __construct(
        string $apiBaseUri,
        ClientInterface $httpClient,
        ?string $accessToken,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        ResultSetFactory $resultSetFactory
    ) {
        $this->requestCookies = $_COOKIE ?? [];
        $this->uriFactory = $uriFactory;
        $this->baseUri = $uriFactory->createUri($apiBaseUri);
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->accessToken = $accessToken;
        $this->resultSetFactory = $resultSetFactory;
    }

    /**
     * @throws PrismicError if an Http Client cannot be discovered and one was not injected.
     * @throws PrismicError if a PSR Request factory cannot be discovered and one was not injected.
     * @throws PrismicError if a PSR URI factory cannot be discovered and one was not injected.
     */
    public static function get(
        string $apiBaseUri,
        ?string $accessToken = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?UriFactoryInterface $uriFactory = null,
        ?ResultSetFactory $resultSetFactory = null
    ) : self {
        $factory = static function ($given, callable $locator, string $message) {
            if ($given) {
                return $given;
            }

            try {
                return $locator();
            } catch (DiscoveryError $error) {
                throw new RuntimeError(
                    $message,
                    $error->getCode(),
                    $error
                );
            }
        };

        return new self(
            $apiBaseUri,
            $factory($httpClient, static function () : ClientInterface {
                return Psr18ClientDiscovery::find();
            }, 'An HTTP client cannot be determined.'),
            (string) $accessToken === '' ? null : $accessToken,
            $factory($requestFactory, static function () : RequestFactoryInterface {
                return Psr17FactoryDiscovery::findRequestFactory();
            }, 'A request factory cannot be determined'),
            $factory($uriFactory, static function () : UriFactoryInterface {
                return Psr17FactoryDiscovery::findUrlFactory();
            }, 'A URI factory cannot be determined'),
            $resultSetFactory ?? new StandardResultSetFactory()
        );
    }

    public function host() : string
    {
        return $this->baseUri->getHost();
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
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw RequestFailure::withClientException($clientException);
        }

        $status = $response->getStatusCode();

        switch ($status) {
            case $status < 300:
                break;

            case $status >= 300 && $status < 400:
                throw RequestFailure::withRedirectResponse($request, $response);

                break;

            case $status >= 400 && $status < 500:
                throw RequestFailure::withClientError($request, $response);

                break;

            case $status >= 500:
                throw RequestFailure::withServerError($request, $response);

                break;
        }

        return $response;
    }

    public function ref() : Ref
    {
        $ref = $this->previewRef();
        if ($ref) {
            return $ref;
        }

        return $this->data()->master();
    }

    public function createQuery(string $form = self::DEFAULT_FORM) : Query
    {
        return (new Query($this->data()->form($form)))
            ->ref($this->ref());
    }

    public function query(Query $query) : ResultSet
    {
        return $this->resultSetFactory->withHttpResponse($this->sendRequest(
            $this->uriFactory->createUri($query->toUrl())
        ));
    }

    public function queryFirst(Query $query) :? Document
    {
        return $this->query($query)->first();
    }

    public function findById(string $id) :? Document
    {
        $query = $this->createQuery()
            ->lang('*')
            ->query(Predicate::at('document.id', $id));

        return $this->queryFirst($query);
    }

    public function findByUid(string $type, string $uid, string $lang = '*') :? Document
    {
        $path = sprintf('my.%s.uid', $type);
        $query = $this->createQuery()
            ->lang($lang)
            ->query(Predicate::at($path, $uid));

        return $this->queryFirst($query);
    }

    public function findByBookmark(string $bookmark) :? Document
    {
        return $this->findById($this->data()->bookmark($bookmark)->documentId());
    }

    /** @param mixed $value */
    private function uriWithQueryValue(UriInterface $uri, string $parameter, $value) : UriInterface
    {
        $params = [];
        parse_str((string) $uri, $params);
        $params[$parameter] = $value;

        return $uri->withQuery(http_build_query($params));
    }

    /** @inheritDoc */
    public function setRequestCookies(array $cookies) : void
    {
        $this->requestCookies = $cookies;
    }

    /**
     * If a preview cookie is set, return the ref stored in that cookie
     */
    private function previewRef() :? Ref
    {
        $cookieNames = [
            str_replace(['.', ' '], '_', self::PREVIEW_COOKIE),
            self::PREVIEW_COOKIE,
        ];
        foreach ($cookieNames as $cookieName) {
            if (! isset($this->requestCookies[$cookieName])) {
                continue;
            }

            return Ref::new(
                'preview',
                $this->requestCookies[$cookieName],
                'Preview',
                false
            );
        }

        return null;
    }

    /**
     * Whether the current ref in use is a preview, i.e. the user is in preview mode
     */
    public function inPreview() : bool
    {
        return $this->previewRef() !== null;
    }

    /**
     * Validate a preview token
     *
     * Preview tokens are an URI provided by the api, normally via a get request to your app. This method ensures that
     * the hostname of the given uri matches the host name of the configured repository as a request to the url will
     * be made in order to start a preview session.
     */
    private function validatePreviewToken(string $token) : UriInterface
    {
        $uri = $this->uriFactory->createUri(urldecode($token));
        /**
         * Because the API host will possibly be name.cdn.prismic.io but the preview domain can be name.prismic.io
         * we can only reliably verify the same parent domain name if we parse both domains with something that uses
         * the public suffix list, like https://github.com/jeremykendall/php-domain-parser for example. We really
         * don't want to have to go through all that, so for now we will just strip/hard-code the 'cdn' part which
         * causes the problem.
         */
        $previewHost = str_replace('.cdn.', '.', $uri->getHost());
        $apiHost = str_replace('.cdn.', '.', $this->baseUri->getHost());
        if ($previewHost !== $apiHost) {
            throw InvalidArgument::mismatchedPreviewHost($this->baseUri, $uri);
        }

        return $uri;
    }

    public function previewSession(string $token) :? DocumentLink
    {
        $uri = $this->validatePreviewToken($token);
        $responseBody = Json::decodeObject((string) $this->sendRequest($uri)->getBody());
        if (isset($responseBody->mainDocument)) {
            $document = $this->findById($responseBody->mainDocument);
            if ($document) {
                return $document->asLink();
            }
        }

        return null;
    }

    public function next(ResultSet $resultSet) :? ResultSet
    {
        if (! $resultSet->nextPage()) {
            return null;
        }

        return $this->resultSetFactory->withHttpResponse(
            $this->sendRequest(
                $this->uriFactory->createUri($resultSet->nextPage())
            )
        );
    }

    public function previous(ResultSet $resultSet) :? ResultSet
    {
        if (! $resultSet->previousPage()) {
            return null;
        }

        return $this->resultSetFactory->withHttpResponse(
            $this->sendRequest(
                $this->uriFactory->createUri($resultSet->previousPage())
            )
        );
    }

    public function findAll(Query $query) : ResultSet
    {
        $resultSet = $this->query($query);
        while ($next = $this->next($resultSet)) {
            $resultSet = $resultSet->merge($next);
        }

        return $resultSet;
    }
}
