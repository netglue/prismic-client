<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class TextElement implements Fragment
{
    /** @var string[] */
    private $tagMap = [
        'heading1' => 'h1',
        'heading2' => 'h2',
        'heading3' => 'h3',
        'heading4' => 'h4',
        'heading5' => 'h5',
        'heading6' => 'h6',
        'paragraph' => 'p',
        'preformatted' => 'pre',
        'o-list-item' => 'li',
        'list-item' => 'li',
    ];

    /** @var string */
    private $type;

    /** @var string|null */
    private $text;

    /** @var mixed[] */
    private $spans;

    /** @var string|null */
    private $label;

    private function __construct(string $type, string $text, iterable $spans, ?string $label)
    {
        $this->type = $type;
        $this->text = $text;
        $this->label = $label;
        $this->spans = $spans;
    }

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

    public function isListItem() : bool
    {
        return $this->type === 'o-list-item' || $this->type === 'list-item';
    }
}
