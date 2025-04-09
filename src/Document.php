<?php

declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Value\DocumentData;
use Prismic\Value\Translation;

interface Document
{
    /**
     * The document unique identifier
     *
     * @return non-empty-string
     */
    public function id(): string;

    /**
     * The unique user document identifier (Unique within a language and a type)
     *
     * It is possible for the uid to be null
     *
     * @return non-empty-string|null
     */
    public function uid(): string|null;

    /**
     * The document type
     *
     * @return non-empty-string
     */
    public function type(): string;

    /** @return list<non-empty-string> */
    public function tags(): iterable;

    /**
     * the document language code such as "en-gb"
     *
     * @return non-empty-string
     */
    public function lang(): string;

    /** The date the document was first published */
    public function firstPublished(): DateTimeInterface;

    /** The last time the document was changed */
    public function lastPublished(): DateTimeInterface;

    /** @return list<Translation> */
    public function translations(): iterable;

    /**
     * Convenience method to return a link to this document that is suitable for passing to a {@link LinkResolver}
     */
    public function asLink(): DocumentLink;

    /**
     * Return the value object containing all the document content fragments
     */
    public function data(): DocumentData;

    /**
     * If the api has been configured with a route for this type of document, the url might be a string
     */
    public function url(): string|null;
}
