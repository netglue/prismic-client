<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Document\Fragment\DocumentLink;

abstract class DefaultLinkResolver implements LinkResolver
{
    public function resolve(Link $link) :? string
    {
        if ($link instanceof UrlLink) {
            return $link->url();
        }

        if ($link instanceof DocumentLink) {
            return $this->resolveDocumentLink($link);
        }

        return null;
    }

    abstract protected function resolveDocumentLink(DocumentLink $link) :? string;
}
