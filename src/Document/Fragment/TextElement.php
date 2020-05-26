<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class TextElement implements Fragment
{
    /** @var string */
    private $type;

    /** @var string|null */
    private $text;

    /** @var Span[] */
    private $spans;

    /** @var string|null */
    private $label;

    /** @param Span[] $spans */
    private function __construct(string $type, string $text, iterable $spans, ?string $label)
    {
        $this->type = $type;
        $this->text = $text;
        $this->label = $label;
        $this->spans = $spans;
    }

    /** @param Span[] $spans */
    public static function new(
        string $type,
        ?string $text,
        iterable $spans,
        ?string $label
    ) : self {
        return new static(
            $type,
            $text ?? '',
            $spans,
            $label
        );
    }

    /** @return Span[] */
    public function spans() : iterable
    {
        return $this->spans;
    }

    public function hasLabel() : bool
    {
        return $this->label !== null;
    }

    public function label() :? string
    {
        return $this->label;
    }

    public function type() : string
    {
        return $this->type;
    }

    public function text() :? string
    {
        return $this->text;
    }

    public function isListItem() : bool
    {
        return $this->isOrderedListItem() || $this->isUnorderedListItem();
    }

    public function isOrderedListItem() : bool
    {
        return $this->type === 'o-list-item';
    }

    public function isUnorderedListItem() : bool
    {
        return $this->type === 'list-item';
    }
}
