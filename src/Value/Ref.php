<?php

declare(strict_types=1);

namespace Prismic\Value;

use Stringable;

use function assert;
use function is_bool;
use function is_string;

/** @psalm-immutable */
final class Ref implements Stringable
{
    /**
     * @param non-empty-string      $id
     * @param non-empty-string      $ref
     * @param non-empty-string|null $label
     */
    private function __construct(
        public readonly string $id,
        public readonly string $ref,
        public readonly string|null $label,
        public readonly bool $isMasterRef,
    ) {
    }

    /**
     * @param non-empty-string      $id
     * @param non-empty-string      $ref
     * @param non-empty-string|null $label
     */
    public static function new(string $id, string $ref, string|null $label, bool $isMasterRef): self
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
        assert($id !== '');
        assert(is_string($ref));
        assert($ref !== '');
        assert(is_string($label) || $label === null);
        $label = $label === '' ? null : $label;
        assert(is_bool($isMaster));

        return self::new($id, $ref, $label, $isMaster);
    }

    /** @return non-empty-string */
    public function id(): string
    {
        return $this->id;
    }

    /** @return non-empty-string */
    public function ref(): string
    {
        return $this->ref;
    }

    /** @return non-empty-string|null */
    public function label(): string|null
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
