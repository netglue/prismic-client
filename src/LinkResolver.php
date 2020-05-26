<?php
declare(strict_types=1);

namespace Prismic;

interface LinkResolver
{
    /**
     * The link resolver should be able to return an uri for any Link instance
     *
     * With the exception of @link DocumentLink instances, other implementations can all return an
     * absolute uri to their location.
     *
     * Implementors should return null if it is not possible to resolve a DocumentLink.
     */
    public function resolve(Link $link) :? string;
}
