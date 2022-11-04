<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Link;

final class Span
{
    private function __construct(
        private string $type,
        private int $start,
        private int $end,
        private string|null $label,
        private Link|null $link,
    ) {
    }

    public static function new(string $type, int $start, int $end, string|null $label, Link|null $link): self
    {
        return new self($type, $start, $end, $label, $link);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function start(): int
    {
        return $this->start;
    }

    public function end(): int
    {
        return $this->end;
    }

    public function label(): string|null
    {
        return $this->label;
    }

    public function link(): Link|null
    {
        return $this->link;
    }
}
