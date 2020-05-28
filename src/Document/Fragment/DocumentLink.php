<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\Link;

final class DocumentLink implements Fragment, Link
{
    /** @var string[] */
    private $tags;
    /** @var string */
    private $id;
    /** @var string|null */
    private $uid;
    /** @var string */
    private $type;
    /** @var string */
    private $lang;
    /** @var string */
    private $slug;
    /** @var bool */
    private $isBroken;

    /** @param string[] $tags */
    private function __construct(
        string $id,
        ?string $uid,
        string $type,
        string $lang,
        string $slug,
        bool $isBroken,
        iterable $tags
    ) {
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->lang = $lang;
        $this->slug = $slug;
        $this->isBroken = $isBroken;
        $this->tags = [];
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    private function addTag(string $tag) : void
    {
        $this->tags[] = $tag;
    }

    /** @param string[] $tags */
    public static function new(
        string $id,
        ?string $uid,
        string $type,
        string $lang,
        string $slug,
        bool $isBroken = false,
        iterable $tags = []
    ) : self {
        return new static($id, $uid, $type, $lang, $slug, $isBroken, $tags);
    }

    public static function withDocument(Document $document) : self
    {
        return new static(
            $document->id(),
            $document->uid(),
            $document->type(),
            $document->lang(),
            $document->slug(),
            false,
            $document->tags()
        );
    }

    public function id() : string
    {
        return $this->id;
    }

    public function uid() :? string
    {
        return $this->uid;
    }

    public function type() : string
    {
        return $this->type;
    }

    public function language() : string
    {
        return $this->lang;
    }

    public function isBroken() : bool
    {
        return $this->isBroken;
    }

    /** @return string[] */
    public function tags() : iterable
    {
        return $this->tags;
    }

    public function slug() : string
    {
        return $this->slug;
    }

    public function __toString() : string
    {
        return $this->id;
    }

    public function isEmpty() : bool
    {
        return false;
    }
}
