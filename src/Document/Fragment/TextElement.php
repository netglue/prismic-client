<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Stringable;

use function in_array;

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

    /** @var Span[] */
    private array $spans;

    /** @param Span[] $spans */
    private function __construct(
        private string $type,
        private string $text,
        iterable $spans,
        private string|null $label,
    ) {
        $this->spans = [];
        foreach ($spans as $span) {
            $this->addSpan($span);
        }
    }

    /** @param Span[] $spans */
    public static function new(
        string $type,
        string|null $text,
        iterable $spans,
        string|null $label,
    ): self {
        return new self(
            $type,
            $text ?? '',
            $spans,
            $label,
        );
    }

    /** @return Span[] */
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

    public function isEmpty(): bool
    {
        return $this->text === '';
    }

    private function addSpan(Span $span): void
    {
        $this->spans[] = $span;
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
