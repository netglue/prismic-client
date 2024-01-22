<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Prismic\Document\Fragment;
use Prismic\Exception\ImageViewNotFound;
use Prismic\Link;
use Traversable;

use function array_keys;
use function count;

/** @template-implements IteratorAggregate<string, Image> */
final class Image implements Fragment, IteratorAggregate, Countable
{
    /** @var array<string, self> */
    private array $views;

    /** @param self[] $views */
    private function __construct(
        private string $name,
        private string $url,
        private int $width,
        private int $height,
        private string|null $alt,
        private string|null $copyright,
        iterable|null $views,
        private Link|null $link,
    ) {
        $this->views = [];
        $this->addView($this);
        if ($views === null) {
            return;
        }

        foreach ($views as $image) {
            $this->addView($image);
        }
    }

    /** @param self[] $views */
    public static function new(
        string $name,
        string $url,
        int $width,
        int $height,
        string|null $alt,
        string|null $copyright,
        iterable|null $views,
        Link|null $linkTo,
    ): self {
        return new self($name, $url, $width, $height, $alt, $copyright, $views, $linkTo);
    }

    private function addView(self $image): void
    {
        $this->views[$image->name] = $image;
    }

    public function viewName(): string
    {
        return $this->name;
    }

    public function getView(string $name): Image
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        throw ImageViewNotFound::withNameAndImage($name, $this);
    }

    /** @return string[] */
    public function knownViews(): array
    {
        return array_keys($this->views);
    }

    public function alt(): string|null
    {
        return $this->alt;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function copyright(): string|null
    {
        return $this->copyright;
    }

    public function linkTo(): Link|null
    {
        return $this->link;
    }

    /** @return Traversable<string, self> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->views);
    }

    public function count(): int
    {
        return count($this->views);
    }

    public function isEmpty(): bool
    {
        return false;
    }
}
