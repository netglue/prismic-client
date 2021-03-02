<?php

declare(strict_types=1);

namespace Prismic\Value;

use function assert;
use function is_string;

final class Language
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    private function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public static function new(string $id, string $name): self
    {
        return new self($id, $name);
    }

    public static function factory(object $object): self
    {
        $id = $object->id ?? null;
        $name = $object->name ?? null;
        assert(is_string($id));
        assert(is_string($name));

        return self::new($id, $name);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
