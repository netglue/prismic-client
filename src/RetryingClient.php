<?php

declare(strict_types=1);

namespace Prismic;

use Prismic\Document\Fragment\DocumentLink;
use Prismic\Exception\RequestFailure;
use Prismic\Value\ApiData;
use Prismic\Value\Ref;

/**
 * @psalm-suppress DeprecatedMethod
 */
final class RetryingClient implements ApiClient
{
    /** @var ApiClient */
    private $wrappedClient;

    /** @var RequestFailure|null */
    private $lastException;

    private function __construct(ApiClient $client)
    {
        $this->wrappedClient = $client;
    }

    public function lastRequestFailure(): ?RequestFailure
    {
        return $this->lastException instanceof RequestFailure ? $this->lastException : null;
    }

    public static function wrap(ApiClient $client): self
    {
        return new self($client);
    }

    public function host(): string
    {
        return $this->wrappedClient->host();
    }

    public function data(): ApiData
    {
        return $this->wrappedClient->data();
    }

    public function ref(): Ref
    {
        return $this->wrappedClient->ref();
    }

    public function createQuery(string $form = self::DEFAULT_FORM): Query
    {
        return $this->wrappedClient->createQuery($form);
    }

    public function query(Query $query): ResultSet
    {
        $this->lastException = null;
        try {
            return $this->wrappedClient->query($query);
        } catch (RequestFailure $error) {
            $this->lastException = $error;
            if ($this->ref()->isMaster()) {
                throw $error;
            }

            return $this->wrappedClient->query(
                $query->ref($this->data()->master())
            );
        }
    }

    public function queryFirst(Query $query): ?Document
    {
        return $this->wrappedClient->queryFirst($query);
    }

    public function findById(string $id): ?Document
    {
        return $this->wrappedClient->findById($id);
    }

    public function findByUid(string $type, string $uid, string $lang = '*'): ?Document
    {
        return $this->wrappedClient->findByUid($type, $uid, $lang);
    }

    public function findByBookmark(string $bookmark): ?Document
    {
        return $this->wrappedClient->findByBookmark($bookmark);
    }

    public function findAll(Query $query): ResultSet
    {
        return $this->wrappedClient->findAll($query);
    }

    public function next(ResultSet $resultSet): ?ResultSet
    {
        return $this->wrappedClient->next($resultSet);
    }

    public function previous(ResultSet $resultSet): ?ResultSet
    {
        return $this->wrappedClient->previous($resultSet);
    }

    /** @inheritDoc */
    public function setRequestCookies(array $cookies): void
    {
        $this->wrappedClient->setRequestCookies($cookies);
    }

    public function inPreview(): bool
    {
        return $this->wrappedClient->inPreview();
    }

    public function previewSession(string $token): ?DocumentLink
    {
        return $this->wrappedClient->previewSession($token);
    }
}
