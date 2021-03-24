<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Stringable;

use function sprintf;

final class GeoPoint implements Fragment, Stringable
{
    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    private function __construct(float $lat, float $lng)
    {
        $this->latitude  = $lat;
        $this->longitude = $lng;
    }

    public static function new(float $lat, float $lng): self
    {
        return new self($lat, $lng);
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function isEmpty(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return sprintf('%0.6f,%0.6f', $this->latitude, $this->longitude);
    }
}
