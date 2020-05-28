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
    /** @var string|null */
    private $provider;
    /** @var string|null */
    private $html;
    /** @var int|null */
    private $width;
    /** @var int|null */
    private $height;

    /** @param mixed[] $attributes */
    private function __construct(
        string $type,
        string $url,
        ?string $provider,
        ?string $html,
        ?int $width,
        ?int $height,
        iterable $attributes
    ) {
        $this->type = $type;
        $this->url = $url;
        $this->provider = $provider;
        $this->html = $html;
        $this->width = $width;
        $this->height = $height;
        $this->attributes = [];
        $this->setAttributes($attributes);
    }

    /** @param mixed[] $attributes */
    public static function new(
        string $type,
        string $url,
        ?string $provider,
        ?string $html,
        ?int $width,
        ?int $height,
        iterable $attributes
    ) : self {
        return new static($type, $url, $provider, $html, $width, $height, $attributes);
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

    public function provider() :? string
    {
        return $this->provider;
    }

    public function html() :? string
    {
        return $this->html;
    }

    public function width() :? int
    {
        return $this->width;
    }

    public function height() :? int
    {
        return $this->height;
    }

    /** @return mixed[] */
    public function attributes() : iterable
    {
        return $this->attributes;
    }
}
