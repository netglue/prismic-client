<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Prismic\Document\FragmentCollection;
use Stringable;

use function array_filter;
use function implode;

use const PHP_EOL;

final class Slice implements Fragment, Stringable
{
    /**
     * @param non-empty-string      $type
     * @param non-empty-string|null $label
     * @param non-empty-string|null $id
     */
    private function __construct(
        private readonly string $type,
        private readonly string|null $label,
        private readonly FragmentCollection $primary,
        private readonly FragmentCollection $items,
        private readonly string|null $variation,
        private readonly string|null $version,
        private readonly string|null $id,
    ) {
    }

    /**
     * @param non-empty-string      $type
     * @param non-empty-string|null $label
     */
    public static function new(
        string $type,
        string|null $label,
        FragmentCollection $primary,
        FragmentCollection $items,
    ): self {
        return new self($type, $label, $primary, $items, null, null, null);
    }

    /**
     * @param non-empty-string      $type
     * @param non-empty-string|null $label
     * @param non-empty-string      $id
     */
    public static function shared(
        string $type,
        string|null $label,
        FragmentCollection $primary,
        FragmentCollection $items,
        string $variation,
        string|null $version,
        string $id,
    ): self {
        return new self($type, $label, $primary, $items, $variation, $version, $id);
    }

    /** @return non-empty-string */
    public function type(): string
    {
        return $this->type;
    }

    /** @return non-empty-string|null */
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

    public function variation(): string|null
    {
        return $this->variation;
    }

    public function version(): string|null
    {
        return $this->version;
    }

    /** @return non-empty-string|null */
    public function id(): string|null
    {
        return $this->id;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->primary->isEmpty() && $this->items->isEmpty();
    }

    #[Override]
    public function __toString(): string
    {
        $buffer = array_filter([
            $this->primary->isEmpty() ? null : (string) $this->primary,
            $this->items->isEmpty() ? null : (string) $this->items,
        ]);

        return implode(PHP_EOL, $buffer);
    }
}
