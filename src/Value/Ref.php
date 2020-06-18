<?php
declare(strict_types=1);

namespace Prismic\Value;

use Stringable;

use function assert;
use function is_bool;
use function is_string;

final class Ref implements Stringable
{
    /** @var string */
    private $id;
    /** @var string */
    private $ref;
    /** @var string */
    private $label;
    /** @var bool */
    private $isMasterRef;

    private function __construct(string $id, string $ref, string $label, bool $isMasterRef)
    {
        $this->id = $id;
        $this->ref = $ref;
        $this->label = $label;
        $this->isMasterRef = $isMasterRef;
    }

    public static function new(string $id, string $ref, string $label, bool $isMasterRef) : self
    {
        return new static($id, $ref, $label, $isMasterRef);
    }

    public static function factory(object $object) : self
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

    public function id() : string
    {
        return $this->id;
    }

    public function ref() : string
    {
        return $this->ref;
    }

    public function label() : string
    {
        return $this->label;
    }

    public function isMaster() : bool
    {
        return $this->isMasterRef;
    }

    public function __toString() : string
    {
        return $this->ref;
    }
}
