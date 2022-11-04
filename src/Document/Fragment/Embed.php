<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Exception\InvalidArgument;

use function is_scalar;

final class Embed implements Fragment
{
    /** @var array<string, int|float|bool|string|null> */
    private array $attributes;

    /** @param iterable<string, int|float|bool|string|null> $attributes */
    private function __construct(
        private string $type,
        private string $url,
        private string|null $provider,
        private string|null $html,
        private int|null $width,
        private int|null $height,
        iterable $attributes,
    ) {
        $this->attributes = [];
        $this->setAttributes($attributes);
    }

    /** @param iterable<string, int|float|bool|string|null> $attributes */
    public static function new(
        string $type,
        string $url,
        string|null $provider,
        string|null $html,
        int|null $width,
        int|null $height,
        iterable $attributes,
    ): self {
        return new self($type, $url, $provider, $html, $width, $height, $attributes);
    }

    /** @param iterable<string, scalar|null> $attributes */
    private function setAttributes(iterable $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    private function setAttribute(string $name, mixed $value): void
    {
        if (! is_scalar($value) && $value !== null) {
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

    public function provider(): string|null
    {
        return $this->provider;
    }

    public function html(): string|null
    {
        return $this->html;
    }

    public function width(): int|null
    {
        return $this->width;
    }

    public function height(): int|null
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

    /**
     * @return string|int|float|bool|null
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     * @todo Add native return type hint in 2.0.0
     */
    public function attribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
}
