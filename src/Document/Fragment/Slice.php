<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class Slice implements Fragment
{
    /** @var string */
    private $type;
    /** @var string|null */
    private $label;
    /** @var Collection */
    private $primary;
    /** @var Collection */
    private $items;

    private function __construct(
        string $type,
        ?string $label,
        Collection $primary,
        Collection $items
    ) {
        $this->type = $type;
        $this->label = $label;
        $this->primary = $primary;
        $this->items = $items;
    }

    public static function new(
        string $type,
        ?string $label,
        Collection $primary,
        Collection $items
    ) : self {
        return new static($type, $label, $primary, $items);
    }

    public function type() : string
    {
        return $this->type;
    }

    public function label() :? string
    {
        return $this->label;
    }

    public function primary() : Collection
    {
        return $this->primary;
    }

    public function items() : Collection
    {
        return $this->items;
    }

    public function isEmpty() : bool
    {
        return $this->primary->isEmpty() && $this->items->isEmpty();
    }
}
