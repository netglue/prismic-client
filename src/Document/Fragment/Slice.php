<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Document\FragmentCollection;
use Stringable;

use function array_filter;
use function implode;

use const PHP_EOL;

final class Slice implements Fragment, Stringable
{
    private function __construct(
        private string $type,
        private string|null $label,
        private FragmentCollection $primary,
        private FragmentCollection $items,
    ) {
    }

    public static function new(
        string $type,
        string|null $label,
        FragmentCollection $primary,
        FragmentCollection $items,
    ): self {
        return new self($type, $label, $primary, $items);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function label(): string|null
    {
        return $this->label;
    }

    public function primary(): FragmentCollection
    {
        return $this->primary;
    }

    public function items(): FragmentCollection
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->primary->isEmpty() && $this->items->isEmpty();
    }

    public function __toString(): string
    {
        $buffer = array_filter([
            $this->primary->isEmpty() ? null : (string) $this->primary,
            $this->items->isEmpty() ? null : (string) $this->items,
        ]);

        return implode(PHP_EOL, $buffer);
    }
}
