<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Prismic\Document\Fragment;
use Prismic\Exception\ImageViewNotFound;
use Prismic\Link;
use function array_keys;
use function count;

final class Image implements Fragment, IteratorAggregate, Countable
{
    /** @var string */
    private $name;
    /** @var string */
    private $url;
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var string|null */
    private $alt;
    /** @var string|null */
    private $copyright;
    /** @var self[] */
    private $views;
    /** @var Link|null */
    private $link;

    /** @param self[] $views */
    private function __construct(string $name, string $url, int $width, int $height, ?string $alt, ?string $copyright, ?iterable $views, ?Link $linkTo)
    {
        $this->name = $name;
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
        $this->alt = $alt;
        $this->copyright = $copyright;
        foreach ($views as $image) {
            $this->addView($image);
        }

        $this->addView($this);
        $this->link = $linkTo;
    }

    /** @param self[] $views */
    public static function new(string $name, string $url, int $width, int $height, ?string $alt, ?string $copyright, ?iterable $views, ?Link $linkTo) : self
    {
        return new static($name, $url, $width, $height, $alt, $copyright, $views, $linkTo);
    }

    private function addView(self $image) : void
    {
        $this->views[$image->name] = $image;
    }

    public function viewName() : string
    {
        return $this->name;
    }

    public function getView(string $name) : Image
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        throw ImageViewNotFound::withNameAndImage($name, $this);
    }

    /** @return string[] */
    public function knownViews() : array
    {
        return array_keys($this->views);
    }

    public function alt() :? string
    {
        return $this->alt;
    }

    public function height() : int
    {
        return $this->height;
    }

    public function width() : int
    {
        return $this->width;
    }

    public function url() : string
    {
        return $this->url;
    }

    public function copyright() :? string
    {
        return $this->copyright;
    }

    public function linkTo() :? Link
    {
        return $this->link;
    }

    /** @return self[] */
    public function getIterator() : iterable
    {
        return new ArrayIterator($this->views);
    }

    public function count() : int
    {
        return count($this->views);
    }
}
