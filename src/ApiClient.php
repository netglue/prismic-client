<?php

declare(strict_types=1);

namespace Prismic;

use Prismic\Document\Fragment\DocumentLink;
use Prismic\Exception\InvalidPreviewToken;
use Prismic\Exception\PreviewTokenExpired;
use Prismic\Exception\RequestFailure;
use Prismic\Value\ApiData;
use Prismic\Value\Ref;

interface ApiClient
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
     *
     * This constant is currently unused because it is no longer possible to run A/B tests with Prismic
     */
    public const EXPERIMENTS_COOKIE = 'io.prismic.experiment';

    /** Return the host name of the api endpoint */
    public function host(): string;

    /** Returns a value object containing current information about the content repository */
    public function data(): ApiData;

    /**
     * Return the current ref in use
     *
     * By default, this is the master ref. Super-global cookies are consulted to detect whether a preview
     * session is active and if so, the preview ref will be preferred.
     */
    public function ref(): Ref;

    /**
     * Create a new query on the current ref
     *
     * Use the query to build your conditions then dispatch it to @link query() to retrieve the results.
     */
    public function createQuery(string $form = self::DEFAULT_FORM): Query;

    /**
     * Submit the given query to the API
     */
    public function query(Query $query): ResultSet;

    /**
     * Convenience method to return the first document for the given query
     */
    public function queryFirst(Query $query): ?Document;

    /**
     * Locate a single document by its unique identifier
     */
    public function findById(string $id): ?Document;

    /**
     * Locate a single document by its type and user unique id
     */
    public function findByUid(string $type, string $uid, string $lang = '*'): ?Document;

    /**
     * Locate the document referenced by the given bookmark
     *
     * @deprecated Will be removed in v2.0
     */
    public function findByBookmark(string $bookmark): ?Document;

    /**
     * Return a result set containing all, un-paginated results for the given query
     */
    public function findAll(Query $query): ResultSet;

    /**
     * Given a paginated result, return the next page of the results, if any
     */
    public function next(ResultSet $resultSet): ?ResultSet;

    /**
     * Given a paginated result, return the previous page of the results, if any
     */
    public function previous(ResultSet $resultSet): ?ResultSet;

    /**
     * Set cookie values found in the request
     *
     * If preview and experiment cookie values are not available in your environment in the $_COOKIE super global, you
     * can provide them here and they'll be inspected to see if a preview is required or an experiment is running
     *
     * @param string[] $cookies
     */
    public function setRequestCookies(array $cookies): void;

    /**
     * Whether the current ref in use is a preview, i.e. the user is in preview mode
     */
    public function inPreview(): bool;

    /**
     * Start a preview session
     *
     * If the preview session can be resolved to a single relevant document, this method will return a document link
     * for that document with which you can construct a url using your {@link LinkResolver} to redirect the user to.
     *
     * @throws InvalidPreviewToken if the token is invalid.
     * @throws InvalidPreviewToken if the token is is not an url.
     * @throws PreviewTokenExpired if the token provided has expired.
     * @throws RequestFailure if an error occurs communicating with the API.
     */
    public function previewSession(string $token): ?DocumentLink;
}
