<?php
declare(strict_types=1);

namespace Prismic\Value;

final class Translation
{
    use DataAssertionBehaviour;

    /** @var string */
    private $id;
    /** @var string */
    private $uid;
    /** @var string */
    private $type;
    /** @var string */
    private $lang;

    private function __construct(string $id, string $uid, string $type, string $lang)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
    }

    public static function new(string $id, string $uid, string $type, string $lang) : self
    {
        return new static($id, $uid, $type, $lang);
    }

    public static function factory(object $object) : self
    {
        return self::new(
            self::assertObjectPropertyIsString($object, 'id'),
            self::assertObjectPropertyIsString($object, 'uid'),
            self::assertObjectPropertyIsString($object, 'type'),
            self::assertObjectPropertyIsString($object, 'lang'),
        );
    }

    public function documentId() : string
    {
        return $this->id;
    }

    public function documentUid() : string
    {
        return $this->uid;
    }

    public function language() : string
    {
        return $this->lang;
    }

    public function documentType() : string
    {
        return $this->type;
    }
}
