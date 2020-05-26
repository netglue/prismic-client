<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\ImageViewNotFound;
use function array_keys;

final class Image implements Fragment
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

    /** @param self[] $views */
    private function __construct(string $name, string $url, int $width, int $height, ?string $alt, ?string $copyright, ?iterable $views)
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
    }

    /** @param self[] $views */
    public static function new(string $name, string $url, int $width, int $height, ?string $alt, ?string $copyright, ?iterable $views) : self
    {
        return new static($name, $url, $width, $height, $alt, $copyright, $views);
    }

    private function addView(self $image) : void
    {
        $this->views[$image->name] = $image;
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
}
