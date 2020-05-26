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

    public function getLatitude() : float
    {
        return $this->latitude;
    }

    public function getLongitude() : float
    {
        return $this->longitude;
    }
}
