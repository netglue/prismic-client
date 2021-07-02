<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Link;

final class Span
{
    /** @var string */
    private $type;
    /** @var int */
    private $start;
    /** @var int */
    private $end;
    /** @var string|null */
    private $label;
    /** @var Link|null */
    private $link;

    private function __construct(string $type, int $start, int $end, ?string $label, ?Link $link)
    {
        $this->type = $type;
        $this->start = $start;
        $this->end = $end;
        $this->label = $label;
        $this->link = $link;
    }

    public static function new(string $type, int $start, int $end, ?string $label, ?Link $link): self
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

    public function label(): ?string
    {
        return $this->label;
    }

    public function link(): ?Link
    {
        return $this->link;
    }
}
