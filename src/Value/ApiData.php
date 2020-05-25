<?php
declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;
use Prismic\Exception\UnexpectedValue;
use Prismic\Exception\UnknownForm;
use function array_keys;
use function array_map;
use function get_object_vars;

final class ApiData implements JsonSerializable
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

    /** @return mixed[] */
    public function jsonSerialize() : array
    {
        return [
            'refs' => $this->refs,
            'bookmarks' => $this->bookmarks,
            'types' => $this->types,
            'languages' => $this->languages,
            'tags' => $this->tags,
            'forms' => $this->forms,
        ];
    }

    public static function factory(object $payload) : ApiData
    {
        $data = new static();
        $data->refs = array_map(static function (object $ref) : Ref {
            return Ref::factory($ref);
        }, self::assertObjectPropertyIsArray($payload, 'refs'));

        $bookmarks = get_object_vars(self::assertObjectPropertyIsObject($payload, 'bookmarks'));
        $data->bookmarks = array_map(static function (string $name, string $id) : Bookmark {
            return Bookmark::new($name, $id);
        }, array_keys($bookmarks), $bookmarks);

        $types = get_object_vars(self::assertObjectPropertyIsObject($payload, 'types'));
        $data->types = array_map(static function (string $id, string $name) : Type {
            return Type::new($id, $name);
        }, array_keys($types), $types);

        $data->languages = array_map(static function (object $lang) : Language {
            return Language::factory($lang);
        }, self::assertObjectPropertyIsArray($payload, 'languages'));

        $data->setTags(...self::assertObjectPropertyIsArray($payload, 'tags'));

        $forms = get_object_vars(self::assertObjectPropertyIsObject($payload, 'forms'));
        $data->forms = array_map(static function (string $id, object $form) : FormSpec {
            return FormSpec::factory($id, $form);
        }, array_keys($forms), $forms);

        return $data;
    }

    private function setTags(string ...$tags) : void
    {
        $this->tags = $tags;
    }

    private function getForm(string $name) :? FormSpec
    {
        foreach ($this->forms as $form) {
            if ($form->id() === $name) {
                return $form;
            }
        }

        return null;
    }

    public function hasForm(string $name) : bool
    {
        return $this->getForm($name) instanceof FormSpec;
    }

    public function form(string $name) : FormSpec
    {
        $form = $this->getForm($name);
        if (! $form) {
            throw UnknownForm::withName($name);
        }

        return $form;
    }

    public function master() : Ref
    {
        foreach ($this->refs as $ref) {
            if (! $ref->isMaster()) {
                continue;
            }

            return $ref;
        }

        throw UnexpectedValue::missingMasterRef();
    }
}
