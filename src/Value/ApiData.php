<?php

declare(strict_types=1);

namespace Prismic\Value;

use Prismic\Exception\UnexpectedValue;
use Prismic\Exception\UnknownBookmark;
use Prismic\Exception\UnknownForm;

use function array_keys;
use function array_map;
use function get_object_vars;

final class ApiData
{
    use DataAssertionBehaviour;

    /** @var Ref[] */
    private $refs;
    /** @var Bookmark[] */
    private $bookmarks;
    /** @var Type[] */
    private $types;
    /** @var Language[] */
    private $languages;
    /** @var string[] */
    private $tags;
    /** @var FormSpec[] */
    private $forms;

    private function __construct()
    {
    }

    public static function factory(object $payload): ApiData
    {
        $data = new self();
        $data->refs = array_map(static function (object $ref): Ref {
            return Ref::factory($ref);
        }, self::assertObjectPropertyIsArray($payload, 'refs'));

        $bookmarks = get_object_vars(self::assertObjectPropertyIsObject($payload, 'bookmarks'));
        $data->bookmarks = array_map(static function (string $name, string $id): Bookmark {
            return Bookmark::new($name, $id);
        }, array_keys($bookmarks), $bookmarks);

        $types = get_object_vars(self::assertObjectPropertyIsObject($payload, 'types'));
        $data->types = array_map(static function (string $id, string $name): Type {
            return Type::new($id, $name);
        }, array_keys($types), $types);

        $data->languages = array_map(static function (object $lang): Language {
            return Language::factory($lang);
        }, self::assertObjectPropertyIsArray($payload, 'languages'));

        $tags = self::optionalArrayProperty($payload, 'tags') ?: [];
        $data->setTags(...$tags);

        $forms = get_object_vars(self::assertObjectPropertyIsObject($payload, 'forms'));
        $data->forms = array_map(static function (string $id, object $form): FormSpec {
            return FormSpec::factory($id, $form);
        }, array_keys($forms), $forms);

        return $data;
    }

    private function setTags(string ...$tags): void
    {
        $this->tags = $tags;
    }

    private function getForm(string $name): ?FormSpec
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

    /**
     * @throws UnknownForm if $name does not correspond to a known form.
     */
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

    public function isBookmarked(string $id): bool
    {
        return $this->bookmarkFromDocumentId($id) instanceof Bookmark;
    }

    public function bookmarkFromDocumentId(string $id): ?Bookmark
    {
        foreach ($this->bookmarks as $bookmark) {
            if ($bookmark->documentId() === $id) {
                return $bookmark;
            }
        }

        return null;
    }

    /**
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

    /** @return Bookmark[] */
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
