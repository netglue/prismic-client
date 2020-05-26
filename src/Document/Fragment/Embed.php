<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;
use function is_scalar;

class Embed implements Fragment
{
    /** @var string */
    private $type;
    /** @var string */
    private $url;
    /** @var mixed[] */
    private $attributes;

    /** @param mixed[] $attributes */
    private function __construct(
        string $type,
        string $url,
        iterable $attributes
    ) {
        $this->type = $type;
        $this->url = $url;
        $this->attributes = [];
        $this->setAttributes($attributes);
    }

    /** @param mixed[] $attributes */
    public static function new(string $type, string $url, iterable $attributes) : self
    {
        return new static($type, $url, $attributes);
    }

    /** @param mixed[] $attributes */
    private function setAttributes(iterable $attributes) : void
    {
        foreach ($attributes as $name => $value) {
            if ($value !== null && ! is_scalar($value)) {
                throw InvalidArgument::scalarExpected($value);
            }

            $this->attributes[$name] = $value;
        }
    }

    public function url() : string
    {
        return $this->url;
    }

    public function type() : string
    {
        return $this->type;
    }
}
