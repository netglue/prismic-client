<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;

final readonly class TableCell implements Fragment
{
    public const string TYPE_HEADER = 'header';
    public const string TYPE_DATA = 'data';

    /**
     * @param non-empty-string                  $key
     * @param self::TYPE_HEADER|self::TYPE_DATA $type
     */
    public function __construct(
        public string $key,
        public string $type,
        public RichText|null $content,
    ) {
    }

    public function isHeaderCell(): bool
    {
        return $this->type === self::TYPE_HEADER;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->content === null || $this->content->isEmpty();
    }
}
