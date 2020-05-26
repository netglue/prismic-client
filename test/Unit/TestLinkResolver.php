<?php
declare(strict_types=1);

namespace PrismicTest;

use Prismic\DefaultLinkResolver;
use Prismic\Document\Fragment\DocumentLink;
use function sprintf;

class TestLinkResolver extends DefaultLinkResolver
{
    protected function resolveDocumentLink(DocumentLink $link) :? string
    {
        if ($link->isBroken()) {
            return null;
        }

        return sprintf('document://%s', $link->uid());
    }
}
