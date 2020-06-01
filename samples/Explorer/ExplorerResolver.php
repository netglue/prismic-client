<?php
declare(strict_types=1);

namespace Prismic\Example\Explorer;

use Prismic\DefaultLinkResolver;
use Prismic\Document\Fragment\DocumentLink;
use function sprintf;

class ExplorerResolver extends DefaultLinkResolver
{
    protected function resolveDocumentLink(DocumentLink $link) :? string
    {
        return sprintf('/?id=%s', $link->id());
    }
}
