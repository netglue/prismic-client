<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;

use function is_scalar;

final class Embed implements Fragment
{
    /** @var string */
    private $type;
    /** @var string */
    private $url;
    /** @var array<string, int|float|bool|string|null> */
    private $attributes;
    /** @var string|null */
    private $provider;
    /** @var string|null */
    private $html;
    /** @var int|null */
    private $width;
    /** @var int|null */
    private $height;

    /** @param iterable<string, int|float|bool|string|null> $attributes */
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

    /** @param iterable<string, int|float|bool|string|null> $attributes */
    public static function new(
        string $type,
        string $url,
        ?string $provider,
        ?string $html,
        ?int $width,
        ?int $height,
        iterable $attributes
    ): self {
        return new self($type, $url, $provider, $html, $width, $height, $attributes);
    }

    /** @param iterable<string, int|string|float|bool|null> $attributes */
    private function setAttributes(iterable $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * @param string|int|float|bool|null $value
     */
    private function setAttribute(string $name, $value): void
    {
        /**
         * @psalm-suppress RedundantConditionGivenDocblockType
         */
        if ($value !== null && ! is_scalar($value)) {
            throw InvalidArgument::scalarExpected($value);
        }

        $this->attributes[$name] = $value;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function provider(): ?string
    {
        return $this->provider;
    }

    public function html(): ?string
    {
        return $this->html;
    }

    public function width(): ?int
    {
        return $this->width;
    }

    public function height(): ?int
    {
        return $this->height;
    }

    /** @return iterable<string, int|float|bool|string|null> */
    public function attributes(): iterable
    {
        return $this->attributes;
    }

    public function isEmpty(): bool
    {
        return false;
    }

    /** @return string|int|float|bool|null */
    public function attribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
}
