<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Value\DocumentData;
use Prismic\Value\Translation;

interface Document
{
    /** The document unique identifier */
    public function id() : string;

    /**
     * The unique user document identifier (Unique within a language and a type)
     *
     * It is possible for the uid to be null
     */
    public function uid() :? string;

    /** The document type */
    public function type() : string;

    /** @return string[] */
    public function tags() : iterable;

    /** the document language code such as "en-gb" */
    public function lang() : string;

    /** The date the document was first published */
    public function firstPublished() : DateTimeInterface;

    /** The last time the document was changed */
    public function lastPublished() : DateTimeInterface;

    /** @return Translation[] */
    public function translations() : iterable;

    /**
     * Convenience method to return a link to this document that is suitable for passing to a {@link LinkResolver}
     */
    public function asLink() : DocumentLink;

    /**
     * Return the value object containing all of the document content fragments
     */
    public function data() : DocumentData;
}
