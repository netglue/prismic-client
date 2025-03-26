<?php

declare(strict_types=1);

namespace PrismicTest;

use Override;
use Prismic\DefaultLinkResolver;
use Prismic\Document\Fragment\DocumentLink;

use function sprintf;

final class TestLinkResolver extends DefaultLinkResolver
{
    #[Override]
    protected function resolveDocumentLink(DocumentLink $link): string|null
    {
        if ($link->isBroken()) {
            return null;
        }

        return sprintf('document://%s', $link->id());
    }
}
