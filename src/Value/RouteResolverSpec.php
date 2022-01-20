<?php

declare(strict_types=1);

namespace Prismic\Value;

use JsonSerializable;
use Prismic\Json;
use Stringable;

use const JSON_FORCE_OBJECT;

final class RouteResolverSpec implements JsonSerializable, Stringable
{
    /** @var string */
    private $type;

    /** @var string */
    private $path;

    /** @var array<string, string> */
    private $resolvers;

    /** @param array<string, string> $resolvers */
    public function __construct(string $type, string $path, array $resolvers)
    {
        $this->type = $type;
        $this->path = $path;
        $this->resolvers = $resolvers;
    }

    public function __toString(): string
    {
        return Json::encode($this, JSON_FORCE_OBJECT);
    }

    /** @return array{type: string, path: string, resolvers: array<string, string>} */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'path' => $this->path,
            'resolvers' => $this->resolvers,
        ];
    }

    /** @param array{type: string, path: string, resolvers: array<string, string>} $data */
    public static function __set_state(array $data): self
    {
        return new self(
            $data['type'],
            $data['path'],
            $data['resolvers']
        );
    }
}
