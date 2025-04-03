<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Override;
use Prismic\Document\Fragment;
use Stringable;
use Traversable;

use function array_map;
use function array_values;
use function explode;
use function implode;
use function in_array;
use function iterator_to_array;

/** @psalm-immutable */
final class TextElement implements Fragment, Stringable
{
    public const TYPE_ORDERED_LIST_ITEM = 'o-list-item';
    public const TYPE_UNORDERED_LIST_ITEM = 'list-item';
    public const TYPE_HEADING1 = 'heading1';
    public const TYPE_HEADING2 = 'heading2';
    public const TYPE_HEADING3 = 'heading3';
    public const TYPE_HEADING4 = 'heading4';
    public const TYPE_HEADING5 = 'heading5';
    public const TYPE_HEADING6 = 'heading6';
    public const TYPE_PARAGRAPH = 'paragraph';
    public const TYPE_PREFORMATTED = 'preformatted';

    /** @var list<Span> */
    private readonly array $spans;

    /**
     * @param self::TYPE_* $type
     * @param list<Span>   $spans
     */
    private function __construct(
        private readonly string $type,
        private readonly string $text,
        array $spans,
        private readonly string|null $label,
    ) {
        $this->spans = array_map(
            static fn (Span $span): Span => $span,
            $spans,
        );
    }

    /**
     * @param self::TYPE_*   $type
     * @param iterable<Span> $spans
     */
    public static function new(
        string $type,
        string|null $text,
        iterable $spans,
        string|null $label,
    ): self {
        $spans = $spans instanceof Traversable
            ? iterator_to_array($spans, false)
            : array_values($spans);

        return new self(
            $type,
            $text ?? '',
            $spans,
            $label,
        );
    }

    /** @return list<Span> */
    public function spans(): iterable
    {
        return $this->spans;
    }

    public function hasLabel(): bool
    {
        return $this->label !== null;
    }

    public function label(): string|null
    {
        return $this->label;
    }

    /** @return self::TYPE_* */
    public function type(): string
    {
        return $this->type;
    }

    public function text(): string|null
    {
        return $this->text;
    }

    public function isListItem(): bool
    {
        return $this->isOrderedListItem() || $this->isUnorderedListItem();
    }

    public function isOrderedListItem(): bool
    {
        return $this->type === self::TYPE_ORDERED_LIST_ITEM;
    }

    public function isUnorderedListItem(): bool
    {
        return $this->type === self::TYPE_UNORDERED_LIST_ITEM;
    }

    public function isHeading(): bool
    {
        return in_array($this->type, [
            self::TYPE_HEADING1,
            self::TYPE_HEADING2,
            self::TYPE_HEADING3,
            self::TYPE_HEADING4,
            self::TYPE_HEADING5,
            self::TYPE_HEADING6,
        ], true);
    }

    public function isParagraph(): bool
    {
        return $this->type === self::TYPE_PARAGRAPH;
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->text === '';
    }

    #[Override]
    public function __toString(): string
    {
        return $this->text;
    }

    /** @param self::TYPE_* $type */
    public function withDifferentType(string $type): self
    {
        return new self(
            $type,
            $this->text,
            $this->spans,
            $this->label,
        );
    }

    /** @param non-empty-string $label */
    public function withReplacementLabel(string $label): self
    {
        return new self(
            $this->type,
            $this->text,
            $this->spans,
            $label,
        );
    }

    /** @param non-empty-string $label */
    public function withAddedLabel(string $label): self
    {
        $labels = explode(' ', (string) $this->label);
        $labels[] = $label;

        return self::withReplacementLabel(implode(' ', $labels));
    }

    public function withoutSpans(): self
    {
        return new self(
            $this->type,
            $this->text,
            [],
            $this->label,
        );
    }
}
