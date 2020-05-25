<?php
declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;

final class Type implements JsonSerializable
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

    public static function new(string $id, string $name) : self
    {
        return new static($id, $name);
    }

    public function id() : string
    {
        return $this->id;
    }

    public function name() : string
    {
        return $this->name;
    }

    /** @return mixed[] */
    public function jsonSerialize() : array
    {
        return [
            $this->id => $this->name,
        ];
    }
}
