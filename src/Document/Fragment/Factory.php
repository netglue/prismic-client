<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use Prismic\Link;
use Prismic\Value\DataAssertionBehaviour;

use function array_filter;
use function array_keys;
use function array_map;
use function assert;
use function count;
use function get_object_vars;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_scalar;
use function preg_match;
use function property_exists;
use function strpos;

final class Factory
{
    use DataAssertionBehaviour;

    public function __invoke(mixed $data): Fragment
    {
        return self::factory($data);
    }

    public static function factory(mixed $data): Fragment
    {
        if (is_scalar($data)) {
            return self::scalarFactory($data);
        }

        if (is_object($data)) {
            return self::objectFactory($data);
        }

        if (is_array($data) && $data !== []) {
            return self::arrayFactory($data);
        }

        return new EmptyFragment();
    }

    private static function scalarFactory(int|float|bool|string $data): Fragment
    {
        if (is_bool($data)) {
            return BooleanFragment::new($data);
        }

        if (is_int($data) || is_float($data)) {
            return Number::new($data);
        }

        if (strpos($data, '#') === 0) {
            return Color::new($data);
        }

        if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $data)) {
            return DateFragment::day($data);
        }

        if (preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}T/', $data)) {
            return DateFragment::fromAtom($data);
        }

        return StringFragment::new($data);
    }

    private static function objectFactory(object $data): Fragment
    {
        if (property_exists($data, 'dimensions')) {
            return self::imageFactory($data);
        }

        if (property_exists($data, 'latitude')) {
            return GeoPoint::new(
                self::assertObjectPropertyIsFloaty($data, 'latitude'),
                self::assertObjectPropertyIsFloaty($data, 'longitude'),
            );
        }

        if (property_exists($data, 'link_type')) {
            return count(get_object_vars($data)) > 1
                ? self::linkFactory($data)
                : new EmptyFragment();
        }

        if (property_exists($data, 'oembed')) {
            return self::embedFactory(self::assertObjectPropertyIsObject($data, 'oembed'));
        }

        if (property_exists($data, 'embed_url')) {
            return self::embedFactory($data);
        }

        if (property_exists($data, 'slice_type')) {
            return self::sliceFactory($data);
        }

        if (property_exists($data, 'spans')) {
            return self::textElementFactory($data);
        }

        if (self::isHash($data) && ! self::isEmptyObject($data)) {
            return Collection::new(array_map(static function ($value): Fragment {
                return self::factory($value);
            }, get_object_vars($data)));
        }

        return new EmptyFragment();
    }

    private static function isHash(object $object): bool
    {
        /** @psalm-suppress RedundantCondition */
        return count(array_filter(array_keys(get_object_vars($object)), '\is_string')) > 0;
    }

    private static function isEmptyObject(object $value): bool
    {
        return count(get_object_vars($value)) === 0;
    }

    private static function imageFactory(object $data, string $name = 'main'): Image
    {
        $values = get_object_vars($data);
        unset(
            $values['dimensions'],
            $values['alt'],
            $values['copyright'],
            $values['url'],
            $values['linkTo'],
            $values['edit'],
        );

        $views = [];
        foreach ($values as $viewName => $view) {
            if (! is_object($view)) {
                continue;
            }

            $views[] = self::imageFactory($view, $viewName);
        }

        $dimensions = self::assertObjectPropertyIsObject($data, 'dimensions');

        $linkTo = property_exists($data, 'linkTo') && is_object($data->linkTo)
            ? self::linkFactory($data->linkTo)
            : null;

        return Image::new(
            $name,
            self::assertObjectPropertyIsString($data, 'url'),
            self::assertObjectPropertyIsInteger($dimensions, 'width'),
            self::assertObjectPropertyIsInteger($dimensions, 'height'),
            self::optionalStringProperty($data, 'alt'),
            self::optionalStringProperty($data, 'copyright'),
            $views,
            $linkTo,
        );
    }

    private static function linkFactory(object $data): Link
    {
        $type = self::assertObjectPropertyIsString($data, 'link_type');

        if ($type === 'Web') {
            return WebLink::new(
                self::assertObjectPropertyIsString($data, 'url'),
                self::optionalStringProperty($data, 'target'),
            );
        }

        $kind = self::optionalStringProperty($data, 'kind');

        if ($type === 'Media' && $kind === 'image') {
            return ImageLink::new(
                self::assertObjectPropertyIsString($data, 'url'),
                self::assertObjectPropertyIsString($data, 'name'),
                self::assertObjectPropertyIsIntegerish($data, 'size'),
                self::assertObjectPropertyIsIntegerish($data, 'width'),
                self::assertObjectPropertyIsIntegerish($data, 'height'),
            );
        }

        if ($type === 'Media') {
            return MediaLink::new(
                self::assertObjectPropertyIsString($data, 'url'),
                self::assertObjectPropertyIsString($data, 'name'),
                self::assertObjectPropertyIsIntegerish($data, 'size'),
            );
        }

        if ($type === 'Document') {
            $isBroken = self::assertObjectPropertyIsBoolean($data, 'isBroken');
            $lang = self::optionalStringProperty($data, 'lang');
            // The language for broken document links is null in some situations
            $lang ??= '*';

            return DocumentLink::new(
                self::assertObjectPropertyIsString($data, 'id'),
                self::optionalStringProperty($data, 'uid'),
                self::assertObjectPropertyIsString($data, 'type'),
                $lang,
                $isBroken,
                self::assertObjectPropertyAllString($data, 'tags'),
            );
        }

        throw InvalidArgument::unknownLinkType($type, $data);
    }

    private static function embedFactory(object $data): Fragment
    {
        $scalars = [];
        foreach (get_object_vars($data) as $name => $attribute) {
            assert(is_scalar($attribute) || $attribute === null);

            $scalars[$name] = $attribute;
        }

        return Embed::new(
            self::assertObjectPropertyIsString($data, 'type'),
            self::assertObjectPropertyIsString($data, 'embed_url'),
            self::optionalStringProperty($data, 'provider_name'),
            self::optionalStringProperty($data, 'html'),
            self::optionalIntegerPropertyOrNull($data, 'width'),
            self::optionalIntegerPropertyOrNull($data, 'height'),
            $scalars,
        );
    }

    private static function sliceFactory(object $data): Fragment
    {
        $items = Collection::new(array_map(static function ($value): Fragment {
            return self::factory($value);
        }, self::assertObjectPropertyIsArray($data, 'items')));

        $primary = get_object_vars(self::assertObjectPropertyIsObject($data, 'primary'));
        $primary = Collection::new(array_map(static function ($value): Fragment {
            return self::factory($value);
        }, $primary));

        return Slice::new(
            self::assertObjectPropertyIsString($data, 'slice_type'),
            self::optionalStringProperty($data, 'slice_label'),
            $primary,
            $items,
        );
    }

    private static function textElementFactory(object $data): Fragment
    {
        return TextElement::new(
            self::assertObjectPropertyIsString($data, 'type'),
            self::optionalStringProperty($data, 'text'),
            array_map(static function (object $span): Span {
                return self::spanFactory($span);
            }, self::assertObjectPropertyIsArray($data, 'spans')),
            self::optionalStringProperty($data, 'label'),
        );
    }

    private static function spanFactory(object $data): Span
    {
        $extra = property_exists($data, 'data') && is_object($data->data) ? $data->data : null;
        $label = null;
        $link = null;
        if ($extra) {
            $label = self::optionalStringProperty($extra, 'label');
            $linkType = self::optionalStringProperty($extra, 'link_type');
            $link = $linkType !== null ? self::linkFactory($extra) : null;
        }

        return Span::new(
            self::assertObjectPropertyIsString($data, 'type'),
            self::assertObjectPropertyIsInteger($data, 'start'),
            self::assertObjectPropertyIsInteger($data, 'end'),
            $label,
            $link,
        );
    }

    /** @param mixed[] $data */
    private static function arrayFactory(array $data): Fragment
    {
        $fragments = [];
        $richText = false;
        /** @psalm-suppress MixedAssignment */
        foreach ($data as $key => $value) {
            $fragment = self::factory($value);
            if ($fragment instanceof TextElement) {
                $richText = true;
            }

            $fragments[$key] = $fragment;
        }

        return $richText
            ? RichText::new($fragments)
            : Collection::new($fragments);
    }
}
