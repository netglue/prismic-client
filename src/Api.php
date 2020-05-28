<?php
declare(strict_types=1);

namespace Prismic;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Prismic\Exception\RequestFailure;
use Prismic\Value\ApiData;
use Prismic\Value\DocumentData;
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

final class Api
{
    /**
     * The default form/collection name to query for results
     */
    public const DEFAULT_FORM = 'everything';

    /**
     * Name of the cookie that will be used to remember the preview reference
     */
    public const PREVIEW_COOKIE = 'io.prismic.preview';

    /**
     * Name of the cookie that will be used to remember the experiment reference
     */
    public const EXPERIMENTS_COOKIE = 'io.prismic.experiment';

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

    private function __construct(
        string $apiBaseUri,
        ClientInterface $httpClient,
        ?string $accessToken,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory
    ) {
        $this->requestCookies = $_COOKIE ?? [];
        $this->uriFactory = $uriFactory;
        $this->baseUri = $uriFactory->createUri($apiBaseUri);
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->accessToken = $accessToken;
    }

    public static function get(
        string $apiBaseUri,
        ?string $accessToken = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?UriFactoryInterface $uriFactory = null
    ) : self {
        return new self(
            $apiBaseUri,
            $httpClient ?? Psr18ClientDiscovery::find(),
            (string) $accessToken === '' ? null : $accessToken,
            $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory(),
            $uriFactory ?? Psr17FactoryDiscovery::findUrlFactory()
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

    /**
     * Return the current ref in use
     *
     * By default, this is the master ref. Super-global cookies are consulted to detect whether a preview
     * session is active and if so, the preview ref will be preferred.
     */
    public function ref() : Ref
    {
        $ref = $this->previewRef();
        if ($ref) {
            return $ref;
        }

        return $this->data()->master();
    }

    /**
     * Create a new query on the current ref
     *
     * Use the query to build your conditions then dispatch it to @link query() to retrieve the results.
     */
    public function createQuery(string $form = self::DEFAULT_FORM) : Query
    {
        return (new Query($this->data()->form($form)))
            ->ref($this->ref());
    }

    /**
     * Submit the given query to the API
     */
    public function query(Query $query) : Response
    {
        return Response::withHttpResponse($this->sendRequest(
            $this->uriFactory->createUri($query->toUrl())
        ));
    }

    /**
     * Convenience method to return the first document for the given query
     */
    public function queryFirst(Query $query) :? DocumentData
    {
        return $this->query($query)->first();
    }

    /**
     * Locate a single document by its unique identifier
     */
    public function findById(string $id) :? DocumentData
    {
        $query = $this->createQuery()
            ->lang('*')
            ->query(Predicate::at('document.id', $id));

        return $this->queryFirst($query);
    }

    /**
     * Locate a single document by its type and user unique id
     */
    public function findByUid(string $type, string $uid, string $lang = '*') :? DocumentData
    {
        $path = sprintf('my.%s.uid', $type);
        $query = $this->createQuery()
            ->lang($lang)
            ->query(Predicate::at($path, $uid));

        return $this->queryFirst($query);
    }

    /**
     * Locate the document referenced by the given bookmark
     */
    public function findByBookmark(string $bookmark) :? DocumentData
    {
        return $this->findById(
            $this->data()->bookmark($bookmark)->documentId()
        );
    }

    /** @param mixed $value */
    private function uriWithQueryValue(UriInterface $uri, string $parameter, $value) : UriInterface
    {
        $params = [];
        parse_str((string) $uri, $params);
        $params[$parameter] = $value;

        return $uri->withQuery(http_build_query($params));
    }

    /**
     * Set cookie values found in the request
     *
     * If preview and experiment cookie values are not available in your environment in the $_COOKIE super global, you
     * can provide them here and they'll be inspected to see if a preview is required or an experiment is running
     *
     * @param string[] $cookies
     */
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

    private function validatePreviewToken() : void
    {
        // @TODO
    }

    public function previewSession() : string
    {
        // @TODO
    }

    public function next(Response $response) :? Response
    {
        // @TODO
    }

    public function previous(Response $response) :? Response
    {
        // @TODO
    }

    public function findAll(Query $query) : Response
    {
        $response = $this->query($query);
        while ($response->getNextPageUrl() !== null) {
            $response = $response->merge(
                Response::withHttpResponse(
                    $this->sendRequest(
                        $this->uriFactory->createUri($response->getNextPageUrl())
                    )
                )
            );
        }

        return $response;
    }
}
