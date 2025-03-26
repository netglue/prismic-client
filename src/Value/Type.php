<?php

declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;
use Override;
use Stringable;

final class Type implements JsonSerializable, Stringable
{
    private function __construct(private string $id, private string $name)
    {
    }

    public static function new(string $id, string $name): self
    {
        return new self($id, $name);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return mixed[] */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            $this->id => $this->name,
        ];
    }

    #[Override]
    public function __toString(): string
    {
        return $this->id;
    }
}
