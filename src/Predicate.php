<?php

declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Exception\InvalidArgument;
use Stringable;

use function array_values;
use function is_array;
use function is_numeric;

use const JSON_UNESCAPED_SLASHES;

/** @psalm-type ArgType = list<scalar|list<scalar>> */
final class Predicate implements Stringable
{
    /**
     * @param list<int|string> $args
     * @psalm-param ArgType $args
     */
    private function __construct(private string $name, private string $fragment, private array $args = [])
    {
    }

    /** @param array{name: string, fragment: string, args: list<int|string>} $data */
    public static function __set_state(array $data): self
    {
        return new self(
            $data['name'],
            $data['fragment'],
            $data['args'],
        );
    }

    public function __toString(): string
    {
        return $this->q();
    }

    public function q(): string
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

    private function serializeField(mixed $value): string
    {
        if (is_array($value)) {
            $value = array_values($value);
        }

        return Json::encode($value, JSON_UNESCAPED_SLASHES);
    }

    /** @param scalar|list<scalar> $value */
    public static function at(string $fragment, mixed $value): self
    {
        return new self('at', $fragment, [$value]);
    }

    public static function hasTag(string $tag): self
    {
        return self::at('document.tags', [$tag]);
    }

    /** @param scalar|list<scalar> $value */
    public static function not(string $fragment, mixed $value): self
    {
        return new self('not', $fragment, [$value]);
    }

    /** @param list<scalar> $values */
    public static function any(string $fragment, array $values): self
    {
        return new self('any', $fragment, [$values]);
    }

    /**
     * The `in` predicate accepts a list of document IDs or document UIDs
     *
     * @param list<string> $values
     */
    public static function in(string $fragment, array $values): self
    {
        return new self('in', $fragment, [$values]);
    }

    public static function has(string $fragment): self
    {
        return new self('has', $fragment);
    }

    public static function missing(string $fragment): self
    {
        return new self('missing', $fragment);
    }

    public static function fulltext(string $fragment, string $value): self
    {
        return new self('fulltext', $fragment, [$value]);
    }

    /**
     * Find Similar Documents
     *
     * The $documentOccurrenceThreshold is defined as the maximum number of documents that a term may appear
     * in to still be considered relevant.
     */
    public static function similar(string $documentId, int $documentOccurrenceThreshold): self
    {
        return new self('similar', $documentId, [$documentOccurrenceThreshold]);
    }

    /**
     * @param int|float|string $lowerBound A number or numeric string
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function lt(string $fragment, $lowerBound): self
    {
        if (! is_numeric($lowerBound)) {
            throw new InvalidArgument(
                'Predicates::lt() expects a number as it’s second argument',
            );
        }

        return new self('number.lt', $fragment, [$lowerBound]);
    }

    /**
     * @param int|float|string $upperBound A number or numeric string
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function gt(string $fragment, $upperBound): self
    {
        if (! is_numeric($upperBound)) {
            throw new InvalidArgument(
                'Predicates::gt() expects a number as it’s second argument',
            );
        }

        return new self('number.gt', $fragment, [$upperBound]);
    }

    /**
     * @param int|float|string $lowerBound A number or numeric string
     * @param int|float|string $upperBound A number or numeric string
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function inRange(string $fragment, $lowerBound, $upperBound): self
    {
        if (! is_numeric($upperBound) || ! is_numeric($lowerBound)) {
            throw new InvalidArgument(
                'Predicates::inRange() expects numbers for it’s second and third arguments',
            );
        }

        return new self('number.inRange', $fragment, [$lowerBound, $upperBound]);
    }

    /**
     * @param DateTimeInterface|int|string $before
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dateBefore(string $fragment, $before): self
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }

        return new self('date.before', $fragment, [$before]);
    }

    /**
     * @param DateTimeInterface|int|string $after
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dateAfter(string $fragment, $after): self
    {
        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }

        return new self('date.after', $fragment, [$after]);
    }

    /**
     * @param DateTimeInterface|int|string $before
     * @param DateTimeInterface|int|string $after
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dateBetween(string $fragment, $before, $after): self
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }

        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }

        return new self('date.between', $fragment, [$before, $after]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfMonth(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new self('date.day-of-month', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfMonthBefore(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new self('date.day-of-month-before', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfMonthAfter(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }

        return new self('date.day-of-month-after', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfWeek(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new self('date.day-of-week', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfWeekBefore(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new self('date.day-of-week-before', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $day
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function dayOfWeekAfter(string $fragment, $day): self
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }

        return new self('date.day-of-week-after', $fragment, [$day]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function month(string $fragment, $month): self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new self('date.month', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function monthBefore(string $fragment, $month): self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new self('date.month-before', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $month
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function monthAfter(string $fragment, $month): self
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }

        return new self('date.month-after', $fragment, [$month]);
    }

    /**
     * @param DateTimeInterface|int|string $year
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function year(string $fragment, $year): self
    {
        if ($year instanceof DateTimeInterface) {
            $year = (int) $year->format('Y');
        }

        return new self('date.year', $fragment, [$year]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function hour(string $fragment, $hour): self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new self('date.hour', $fragment, [$hour]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function hourBefore(string $fragment, $hour): self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new self('date.hour-before', $fragment, [$hour]);
    }

    /**
     * @param DateTimeInterface|int|string $hour
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @todo Add native type hint in 2.0.0
     */
    public static function hourAfter(string $fragment, $hour): self
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }

        return new self('date.hour-after', $fragment, [$hour]);
    }

    /** @param float $radius In Kilometers */
    public static function near(string $fragment, float $latitude, float $longitude, float $radius): self
    {
        return new self('geopoint.near', $fragment, [$latitude, $longitude, $radius]);
    }
}
