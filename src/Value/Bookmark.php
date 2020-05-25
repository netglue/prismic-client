<?php
declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;

final class Bookmark implements JsonSerializable
{
    /** @var string */
    private $name;
    /** @var string */
    private $id;

    private function __construct(string $name, string $id)
    {
        $this->name = $name;
        $this->id = $id;
    }

    public static function new(string $name, string $id) : self
    {
        return new static($name, $id);
    }

    public function name() : string
    {
        return $this->name;
    }

    public function documentId() : string
    {
        return $this->id;
    }

    /** @return mixed[] */
    public function jsonSerialize() : array
    {
        return [
            $this->name => $this->id,
        ];
    }
}
