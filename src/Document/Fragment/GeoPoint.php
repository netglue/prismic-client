<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class GeoPoint implements Fragment
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

    public static function new(float $lat, float $lng) : self
    {
        return new static($lat, $lng);
    }

    public function latitude() : float
    {
        return $this->latitude;
    }

    public function longitude() : float
    {
        return $this->longitude;
    }

    public function isEmpty() : bool
    {
        return false;
    }
}
