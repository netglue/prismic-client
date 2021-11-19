<?php

declare(strict_types=1);

namespace Prismic\Serializer;

use DateTimeInterface;
use DateTimeZone;
use Laminas\Escaper\Escaper;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\BooleanFragment;
use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\DateFragment;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\Embed;
use Prismic\Document\Fragment\EmptyFragment;
use Prismic\Document\Fragment\GeoPoint;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\ImageLink;
use Prismic\Document\Fragment\ListItems;
use Prismic\Document\Fragment\MediaLink;
use Prismic\Document\Fragment\Number;
use Prismic\Document\Fragment\OrderedList;
use Prismic\Document\Fragment\Slice;
use Prismic\Document\Fragment\Span;
use Prismic\Document\Fragment\StringFragment;
use Prismic\Document\Fragment\TextElement;
use Prismic\Document\Fragment\WebLink;
use Prismic\Document\FragmentCollection;
use Prismic\Exception\UnexpectedValue;
use Prismic\Link;
use Prismic\LinkResolver;

use function array_filter;
use function array_keys;
use function array_map;
use function array_walk;
use function assert;
use function count;
use function date_default_timezone_get;
use function get_class;
use function implode;
use function is_array;
use function is_string;
use function nl2br;
use function preg_split;
use function sprintf;

use const PREG_SPLIT_NO_EMPTY;

class HtmlSerializer
{
    /** @var string */
    private $dateFormat = 'l jS F Y';
    /** @var string */
    private $dateTimeFormat = 'l jS F Y H:i:s';
    /** @var DateTimeZone */
    private $timezone;
    /** @var Escaper */
    private $escaper;
    /** @var LinkResolver */
    private $resolver;
    /** @var string[] */
    private $tagMap = [
        TextElement::TYPE_HEADING1 => 'h1',
        TextElement::TYPE_HEADING2 => 'h2',
        TextElement::TYPE_HEADING3 => 'h3',
        TextElement::TYPE_HEADING4 => 'h4',
        TextElement::TYPE_HEADING5 => 'h5',
        TextElement::TYPE_HEADING6 => 'h6',
        TextElement::TYPE_PARAGRAPH => 'p',
        TextElement::TYPE_PREFORMATTED => 'pre',
        TextElement::TYPE_ORDERED_LIST_ITEM => 'li',
        TextElement::TYPE_UNORDERED_LIST_ITEM => 'li',
    ];

    public function __construct(LinkResolver $resolver)
    {
        $this->resolver = $resolver;
        $this->timezone = new DateTimeZone(date_default_timezone_get());
        $this->escaper = new Escaper();
    }

    public function __invoke(Fragment $fragment): string
    {
        if ($fragment instanceof ListItems) {
            return $this->listItems($fragment);
        }

        if ($fragment instanceof FragmentCollection) {
            $output = '';
            foreach ($fragment as $item) {
                $output .= $this($item);
            }

            return $output;
        }

        return $this->serializeFragment($fragment);
    }

    private function serializeFragment(Fragment $fragment): string
    {
        switch (get_class($fragment)) {
            case BooleanFragment::class:
                assert($fragment instanceof BooleanFragment);

                return sprintf(
                    '<kbd class="boolean">%s</kbd>',
                    $fragment() ? 'true' : 'false'
                );

            case Number::class:
                assert($fragment instanceof Number);

                return sprintf(
                    '<kbd class="number">%d</kbd>',
                    (string) $fragment
                );

            case EmptyFragment::class:
                return '';

            case DateFragment::class:
                assert($fragment instanceof DateFragment);

                return $this->date($fragment);

            case StringFragment::class:
                assert($fragment instanceof StringFragment);

                return sprintf(
                    '<kbd>%s</kbd>',
                    $this->escaper->escapeHtml((string) $fragment)
                );

            case GeoPoint::class:
                assert($fragment instanceof GeoPoint);

                return sprintf(
                    '<span class="geopoint" data-latitude="%1$s" data-longitude="%2$s">%1$s, %2$s</span>',
                    $fragment->latitude(),
                    $fragment->latitude()
                );

            case Color::class:
                assert($fragment instanceof Color);

                return sprintf(
                    '<kbd class="color" style="background-color: %1$s; color: %2$s">%1$s</kbd>',
                    (string) $fragment,
                    (string) $fragment->invert()
                );

            case Image::class:
                assert($fragment instanceof Image);

                return $this->image($fragment);

            case WebLink::class:
            case DocumentLink::class:
            case ImageLink::class:
            case MediaLink::class:
                assert($fragment instanceof Link);

                return $this->link($fragment);

            case Slice::class:
                assert($fragment instanceof Slice);

                return $this->slice($fragment);

            case TextElement::class:
                assert($fragment instanceof TextElement);

                return $this->textElement($fragment);

            case Embed::class:
                assert($fragment instanceof Embed);

                return $this->embed($fragment);
        }

        throw new UnexpectedValue(sprintf(
            'I don’t know how to serialize %s instances',
            get_class($fragment)
        ));
    }

    private function date(DateFragment $fragment): string
    {
        $date = ! $fragment->isDay() ? $fragment->setTimezone($this->timezone) : $fragment;

        return sprintf(
            '<time datetime="%s">%s</time>',
            $date->format($fragment->isDay() ? 'Y-m-d' : DateTimeInterface::ATOM),
            $date->format($fragment->isDay() ? $this->dateFormat : $this->dateTimeFormat)
        );
    }

    private function listItems(ListItems $htmlList): string
    {
        if (! count($htmlList)) {
            return '';
        }

        $items = [];
        foreach ($htmlList as $item) {
            $items[] = $this($item);
        }

        return sprintf(
            '<%1$s>%2$s</%1$s>',
            $htmlList instanceof OrderedList ? 'ol' : 'ul',
            implode('', $items)
        );
    }

    /** @param mixed[] $attributes */
    private function htmlAttributes(array $attributes): string
    {
        $atrs = implode(' ', array_map(function (string $atr, $value): string {
            return sprintf('%s="%s"', $atr, $this->escaper->escapeHtml((string) $value));
        }, array_keys($attributes), $attributes));

        return empty($atrs) ? '' : ' ' . $atrs;
    }

    private function linkOpenTag(Link $link): ?string
    {
        $url = $this->resolver->resolve($link);
        if (! $url) {
            return null;
        }

        $attributes = array_filter([
            'href' => $url,
            'target' => $link instanceof WebLink ? $link->target() : null,
        ]);

        return sprintf('<a%s>', $this->htmlAttributes($attributes));
    }

    private function link(Link $link, ?string $wraps = null): string
    {
        $openTag = $this->linkOpenTag($link);
        if (! $openTag) {
            return $wraps ?: '';
        }

        return sprintf(
            '%s%s</a>',
            $openTag,
            $wraps ?: (string) $link
        );
    }

    private function image(Image $fragment): string
    {
        $attributes = array_filter([
            'src' => $fragment->url(),
            'width' => $fragment->width(),
            'height' => $fragment->height(),
            'alt' => $fragment->alt(),
        ]);

        $img = sprintf('<img%s>', $this->htmlAttributes($attributes));

        return $fragment->linkTo()
            ? $this->link($fragment->linkTo(), $img)
            : $img;
    }

    private function slice(Slice $fragment): string
    {
        $primary = $this($fragment->primary());
        $items   = $this($fragment->items());
        if (empty($primary) && empty($items)) {
            return '';
        }

        $attributes = array_filter([
            'data-slice-type' => $fragment->type(),
            'class' => $fragment->label(),
        ]);

        return sprintf(
            '<div%s>%s%s</div>',
            $this->htmlAttributes($attributes),
            $primary,
            $items
        );
    }

    private function textElement(TextElement $fragment): string
    {
        if (empty($fragment->text())) {
            return '';
        }

        $attributes = $this->htmlAttributes(array_filter([
            'class' => $fragment->label(),
        ]));

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $this->tagMap[$fragment->type()],
            $attributes,
            $this->insertSpans($fragment, $fragment->text(), $fragment->spans())
        );
    }

    /** @param Span[] $spans */
    private function insertSpans(TextElement $fragment, string $text, array $spans): string
    {
        $wrapper = $fragment->type() === 'preformatted'
            ? static function (string $str): string {
                return $str;
            }
        : static function (string $str): string {
            return nl2br($str);
        };

        if (empty($spans) || empty($text)) {
            return $wrapper($this->escaper->escapeHtml($text));
        }

        $nodes = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($nodes)) {
            return '';
        }

        array_walk($nodes, function (string &$character): void {
            $character = $this->escaper->escapeHtml($character);
        });

        foreach ($spans as $span) {
            $openTag = $closeTag = null;
            $end = $span->end() - 1;
            $start = $span->start();
            switch ($span->type()) {
                case 'strong':
                case 'em':
                    $openTag  = sprintf('<%s>', $span->type());
                    $closeTag = sprintf('</%s>', $span->type());
                    break;

                case 'label':
                    // Multiple labels at the same indexes are not possible,
                    // therefore we don't have to combine CSS classes
                    $openTag  = sprintf('<span%s>', $this->htmlAttributes(['class' => $span->label()]));
                    $closeTag = '</span>';
                    break;

                case 'hyperlink':
                    if ($span->link()) {
                        $openTag  = $this->linkOpenTag($span->link());
                        $closeTag = '</a>';
                    }

                    break;
            }

            if (! $openTag || ! $closeTag) {
                continue;
            }

            assert(is_string($nodes[$start]));
            assert(is_string($nodes[$end]));

            $nodes[$start] = sprintf('%s%s', $openTag, $nodes[$start]);
            $nodes[$end] = sprintf('%s%s', $nodes[$end], $closeTag);
        }

        /** @psalm-var array<array-key, string> $nodes */
        return $wrapper(implode('', $nodes));
    }

    private function embed(Embed $embed): string
    {
        $attributes = $this->htmlAttributes(array_filter([
            'data-oembed-provider' => $embed->provider(),
            'data-oembed-type' => $embed->type(),
            'data-oembed-url' => $embed->url(),
            'data-oembed-width' => $embed->width(),
            'data-oembed-height' => $embed->height(),
        ]));

        return sprintf(
            '<div%1$s>%2$s</div>',
            $attributes,
            $embed->html()
        );
    }
}
