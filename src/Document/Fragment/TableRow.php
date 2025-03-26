<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use IteratorAggregate;
use Override;
use Prismic\Document\Fragment;
use Traversable;

/** @implements IteratorAggregate<int, TableCell> */
final readonly class TableRow implements Fragment, IteratorAggregate
{
    /**
     * @param non-empty-string          $key
     * @param non-empty-list<TableCell> $cells
     */
    public function __construct(
        public string $key,
        public array $cells,
    ) {
    }

    #[Override]
    public function isEmpty(): bool
    {
        foreach ($this->cells as $cell) {
            if ($cell->isEmpty()) {
                continue;
            }

            return false;
        }

        return true;
    }

    /** @return Traversable<int, TableCell> */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cells);
    }
}
