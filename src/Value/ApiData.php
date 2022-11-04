<?php

declare(strict_types=1);

namespace Prismic\Value;

use Prismic\Exception\UnexpectedValue;
use Prismic\Exception\UnknownBookmark;
use Prismic\Exception\UnknownForm;

use function array_keys;
use function array_map;
use function get_object_vars;

/** @psalm-suppress DeprecatedClass, DeprecatedMethod */
final class ApiData
{
    use DataAssertionBehaviour;

    /**
     * @param array<array-key, Ref>      $refs
     * @param array<array-key, Type>     $types
     * @param array<array-key, Language> $languages
     * @param array<array-key, FormSpec> $forms
     * @param array<array-key, string>   $tags
     * @param array<array-key, Bookmark> $bookmarks
     */
    private function __construct(
        private array $refs,
        private array $types,
        private array $languages,
        private array $forms,
        private array $tags,
        private array $bookmarks,
    ) {
    }

    public static function factory(object $payload): ApiData
    {
        $bookmarks = get_object_vars(self::assertObjectPropertyIsObject($payload, 'bookmarks'));
        $types = get_object_vars(self::assertObjectPropertyIsObject($payload, 'types'));
        /** @var string[] $tags */
        $tags = self::optionalArrayProperty($payload, 'tags') ?: [];
        $forms = get_object_vars(self::assertObjectPropertyIsObject($payload, 'forms'));

        return new self(
            array_map(static function (object $ref): Ref {
                return Ref::factory($ref);
            }, self::assertObjectPropertyIsArray($payload, 'refs')),
            array_map(static function (string $id, string $name): Type {
                return Type::new($id, $name);
            }, array_keys($types), $types),
            array_map(static function (object $lang): Language {
                return Language::factory($lang);
            }, self::assertObjectPropertyIsArray($payload, 'languages')),
            array_map(static function (string $id, object $form): FormSpec {
                return FormSpec::factory($id, $form);
            }, array_keys($forms), $forms),
            $tags,
            array_map(static function (string $name, string $id): Bookmark {
                return Bookmark::new($name, $id);
            }, array_keys($bookmarks), $bookmarks),
        );
    }

    private function getForm(string $name): FormSpec|null
    {
        foreach ($this->forms as $form) {
            if ($form->id() === $name) {
                return $form;
            }
        }

        return null;
    }

    public function hasForm(string $name): bool
    {
        return $this->getForm($name) instanceof FormSpec;
    }

    /** @throws UnknownForm if $name does not correspond to a known form. */
    public function form(string $name): FormSpec
    {
        $form = $this->getForm($name);
        if (! $form) {
            throw UnknownForm::withName($name);
        }

        return $form;
    }

    public function master(): Ref
    {
        foreach ($this->refs as $ref) {
            if (! $ref->isMaster()) {
                continue;
            }

            return $ref;
        }

        throw UnexpectedValue::missingMasterRef();
    }

    /**
     * @deprecated
     *
     * @return string[]
     */
    public function tags(): iterable
    {
        return $this->tags;
    }

    /** @deprecated Bookmarks are deprecated - Removal in v2.0. */
    public function isBookmarked(string $id): bool
    {
        return $this->bookmarkFromDocumentId($id) instanceof Bookmark;
    }

    /** @deprecated Bookmarks are deprecated - Removal in v2.0. */
    public function bookmarkFromDocumentId(string $id): Bookmark|null
    {
        foreach ($this->bookmarks as $bookmark) {
            if ($bookmark->documentId() === $id) {
                return $bookmark;
            }
        }

        return null;
    }

    /**
     * @deprecated Bookmarks are deprecated - Removal in v2.0.
     *
     * @throws UnknownBookmark if $name does not correspond to a known bookmark.
     */
    public function bookmark(string $name): Bookmark
    {
        foreach ($this->bookmarks as $bookmark) {
            if ($bookmark->name() !== $name) {
                continue;
            }

            return $bookmark;
        }

        throw UnknownBookmark::withName($name);
    }

    /** @return Type[] */
    public function types(): iterable
    {
        return $this->types;
    }

    /**
     * @deprecated Bookmarks are deprecated - Removal in v2.0.
     *
     * @return Bookmark[]
     */
    public function bookmarks(): iterable
    {
        return $this->bookmarks;
    }

    /** @return Language[] */
    public function languages(): iterable
    {
        return $this->languages;
    }
}
