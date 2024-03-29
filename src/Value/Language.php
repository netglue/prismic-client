<?php

declare(strict_types=1);

namespace Prismic\Value;

use Stringable;

use function assert;
use function is_string;

final class Language implements Stringable
{
    private function __construct(private string $id, private string $name)
    {
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

    public function __toString(): string
    {
        return $this->id;
    }
}
