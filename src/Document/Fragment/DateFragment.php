<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use DateTimeImmutable;
use DateTimeZone;
use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;

final class DateFragment extends DateTimeImmutable implements Fragment
{
    /** @var bool */
    private $isDay = false;

    public function isDay() : bool
    {
        return $this->isDay;
    }

    public static function day(string $value) : self
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));
        if (! $date instanceof DateTimeImmutable) {
            throw InvalidArgument::invalidDateFormat('Y-m-d', $value);
        }

        $fragment = self::fromDate($date);
        $fragment->isDay = true;

        return $fragment;
    }

    public static function fromAtom(string $value) : self
    {
        $date = DateTimeImmutable::createFromFormat(self::ATOM, $value, new DateTimeZone('UTC'));
        if (! $date instanceof DateTimeImmutable) {
            throw InvalidArgument::invalidDateFormat(self::ATOM, $value);
        }

        return self::fromDate($date);
    }

    private static function fromDate(DateTimeImmutable $date) : self
    {
        return (new self())->setTimestamp($date->getTimestamp())->setTimezone($date->getTimezone());
    }
}
