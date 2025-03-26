<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;

use function count;

final readonly class Table implements Fragment
{
    /** @param list<TableRow> $body */
    public function __construct(
        public TableRow|null $head,
        public array $body,
    ) {
    }

    #[Override]
    public function isEmpty(): bool
    {
        if ($this->head !== null && ! $this->head->isEmpty()) {
            return false;
        }

        foreach ($this->body as $row) {
            if ($row->isEmpty()) {
                continue;
            }

            return false;
        }

        return true;
    }

    /** @psalm-assert-if-true TableRow $this->head */
    public function hasHead(): bool
    {
        return $this->head !== null;
    }

    /** @psalm-assert-if-true non-empty-list<TableRow> $this->body */
    public function hasBody(): bool
    {
        return count($this->body) > 0;
    }
}
