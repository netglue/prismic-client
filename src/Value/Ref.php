<?php

declare(strict_types=1);

namespace Prismic\Value;

use Stringable;

use function assert;
use function is_bool;
use function is_string;

final class Ref implements Stringable
{
    private function __construct(
        private string $id,
        private string $ref,
        private string $label,
        private bool $isMasterRef,
    ) {
    }

    public static function new(string $id, string $ref, string $label, bool $isMasterRef): self
    {
        return new self($id, $ref, $label, $isMasterRef);
    }

    public static function factory(object $object): self
    {
        $id = $object->id ?? null;
        $ref = $object->ref ?? null;
        $label = $object->label ?? null;
        $isMaster = $object->isMasterRef ?? false;
        assert(is_string($id));
        assert(is_string($ref));
        assert(is_string($label));
        assert(is_bool($isMaster));

        return self::new($id, $ref, $label, $isMaster);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function ref(): string
    {
        return $this->ref;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isMaster(): bool
    {
        return $this->isMasterRef;
    }

    public function __toString(): string
    {
        return $this->ref;
    }
}
