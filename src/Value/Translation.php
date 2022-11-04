<?php

declare(strict_types=1);

namespace Prismic\Value;

final class Translation
{
    use DataAssertionBehaviour;

    private function __construct(
        private string $id,
        private string|null $uid,
        private string $type,
        private string $lang,
    ) {
    }

    public static function new(string $id, string|null $uid, string $type, string $lang): self
    {
        return new self($id, $uid, $type, $lang);
    }

    public static function factory(object $object): self
    {
        return self::new(
            self::assertObjectPropertyIsString($object, 'id'),
            self::optionalStringProperty($object, 'uid'),
            self::assertObjectPropertyIsString($object, 'type'),
            self::assertObjectPropertyIsString($object, 'lang'),
        );
    }

    public function documentId(): string
    {
        return $this->id;
    }

    public function documentUid(): string|null
    {
        return $this->uid;
    }

    public function language(): string
    {
        return $this->lang;
    }

    public function documentType(): string
    {
        return $this->type;
    }
}
