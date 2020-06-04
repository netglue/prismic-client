<?php
declare(strict_types=1);

namespace Prismic\Example;

use Prismic\DefaultLinkResolver;
use Prismic\Document\Fragment\DocumentLink;
use function sprintf;

class LinkResolverExample extends DefaultLinkResolver
{
    protected function resolveDocumentLink(DocumentLink $link) :? string
    {
        /**
         * The document link provided has the following methods to help construct your
         * application specific URL
         *
         * $link->id()   - Universally Unique Document Identifier, i.e. Wxy8hRtk_r
         * $link->uid()  - Unique to each TYPE of document, i.e. 'about-us'.
         *               - The UID is guaranteed to exist if your document type has a specific UID field
         * $link->type() - The type of document, i.e. 'web-page'
         * $link->tags() - An array of tags where each element is a string
         * $link->lang() - The language of the document
         */

        return sprintf('/app/%s/%s', $link->type(), $link->uid());
    }
}
