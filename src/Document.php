<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;

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

    /** @return string[] */
    public function slugs() : iterable;

    /**
     * The document slug
     *
     * Normally the most recent (first) slug found. Often the same as the uid.
     * A string can be guaranteed because the API <strong>always</strong> assigns a slug to every document.
     */
    public function slug() : string;

    /** the document language code such as "en-gb" */
    public function lang() : string;

    /** The date the document was first published */
    public function firstPublished() : DateTimeInterface;

    /** The last time the document was changed */
    public function lastPublished() : DateTimeInterface;
}
