<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Exception\InvalidArgument;
use Stringable;
use function implode;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;

final class Predicate implements Stringable
{
    /** @var string  */
    private $name;

    /** @var string  */
    private $fragment;

    /** @var mixed[] */
    private $args;

    /**
     * @param mixed[] $args
     */
    private function __construct(string $name, string $fragment, array $args = [])
    {
        $this->name     = $name;
        $this->fragment = $fragment;
        $this->args     = $args;
    }

    public function __toString() : string
    {
        return $this->q();
    }

    public function q() : string
    {
        $query = '[:d = ' . $this->name . '(';
        if ($this->name === 'similar') {
            $query .= '"' . $this->fragment . '"';
        } else {
            $query .= $this->fragment;
        }

        foreach ($this->args as $arg) {
            $query .= ', ' . $this->serializeField($arg);
        }

        $query .= ')]';

        return $query;
    }

    /**
     * @param mixed $value
     */
    private function serializeField($value) : string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            $fields = [];
            foreach ($value as $elt) {
                $fields[] = $this->serializeField($elt);
            }

            return '[' . implode(', ', $fields) . ']';
        }

        return (string) $value;
    }

    /**
     * @param string|string[] $value
     */
    public static function at(string $fragment, $value) : self
    {
        return new static('at', $fragment, [$value]);
    }

    /**
     * @param string|string[] $value
     */
    public static function not(string $fragment, $value) : self
    {
        return new static('not', $fragment, [$value]);
    }

    /**
     * @param mixed[] $values
     */
    public static function any(string $fragment, array $values) : self
    {
        return new static('any', $fragment, [$values]);
    }

    /**
     * @param mixed[] $values
     */
    public static function in(string $fragment, array $values) : self
    {
        return new static('in', $fragment, [$values]);
    }

    public static function has(string $fragment) : self
    {
        return new static('has', $fragment);
    }

    public static function missing(string $fragment) : self
    {
        return new static('missing', $fragment);
    }

    public static function fulltext(string $fragment, string $value) : self
    {
        return new static('fulltext', $fragment, [$value]);
    }

    public static function similar(string $documentId, int $maxResults) : self
    {
        return new static('similar', $documentId, [$maxResults]);
    }

    /**
     * @param int|float|string $lowerBound A number or numeric string
     */
    public static function lt(string $fragment, $lowerBound) : self
    {
        if (! is_numeric($lowerBound)) {
            throw new InvalidArgument(
                'Predicates::lt() expects a number as it’s second argument'
            );
        }

        return new static('number.lt', $fragment, [$lowerBound]);
    }

    /**
     * @param int|float|string $upperBound A number or numeric string
     */
    public static function gt(string $fragment, $upperBound) : self
    {
        if (! is_numeric($upperBound)) {
            throw new InvalidArgument(
                'Predicates::gt() expects a number as it’s second argument'
            );
        }

        return new static('number.gt', $fragment, [$upperBound]);
    }

    /**
     * @param int|float|string $lowerBound A number or numeric string
     * @param int|float|string $upperBound A number or numeric string
     */
    public static function inRange(string $fragment, $lowerBound, $upperBound) : self
    {
        if (! is_numeric($upperBound) || ! is_numeric($lowerBound)) {
            throw new InvalidArgument(
                'Predicates::inRange() expects numbers for it’s second and third arguments'
            );
        }

        return new static('number.inRange', $fragment, [$lowerBound, $upperBound]);
    }

    /**
     * @param DateTimeInterface|int|string $before
     */
    public static function dateBefore(string $fragment, $before) : self
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }

        return new static('date.before', $fragment, [$before]);
    }

    /**
     * @param DateTimeInterface|int|string $after
     */
    public static function dateAfter(string $fragment, $after) : self
    {
        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }

        return new static('date.after', $fragment, [$after]);
    }

    /**
     * @param DateTimeInterface|int|string $before
     * @param DateTimeInterface|int|string $after
     */
    public static function dateBetween(string $fragment, $before, $after) : self
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }

        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }

        return new static('date.between', $fragment, [$before, $after]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfMonth(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new static('date.day-of-month', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfMonthBefore(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new static('date.day-of-month-before', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfMonthAfter(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new static('date.day-of-month-after', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfWeek(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new static('date.day-of-week', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfWeekBefore(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new static('date.day-of-week-before', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     */
    public static function dayOfWeekAfter(string $fragment, $day) : self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new static('date.day-of-week-after', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     */
    public static function month(string $fragment, $month) : self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new static('date.month', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     */
    public static function monthBefore(string $fragment, $month) : self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new static('date.month-before', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     */
    public static function monthAfter(string $fragment, $month) : self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new static('date.month-after', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $year
     */
    public static function year(string $fragment, $year) : self
    {
        if ($year instanceof DateTimeInterface) {
            $year = (int) $year->format('Y');
        }

        return new static('date.year', $fragment, [$year]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     */
    public static function hour(string $fragment, $hour) : self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new static('date.hour', $fragment, [$hour]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     */
    public static function hourBefore(string $fragment, $hour) : self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new static('date.hour-before', $fragment, [$hour]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     */
    public static function hourAfter(string $fragment, $hour) : self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new static('date.hour-after', $fragment, [$hour]);
    }

    /**
     * @param float $radius In Kilometers
     */
    public static function near(string $fragment, float $latitude, float $longitude, float $radius) : self
    {
        return new static('geopoint.near', $fragment, [$latitude, $longitude, $radius]);
    }
}
