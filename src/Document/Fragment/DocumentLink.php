<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;

final class DocumentLink implements Fragment
{
    /** @var string[] */
    private $tags;
    /** @var string */
    private $id;
    /** @var string */
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
        string $uid,
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
        string $uid,
        string $type,
        string $lang,
        string $slug,
        bool $isBroken = false,
        iterable $tags = []
    ) : self {
        return new static($id, $uid, $type, $lang, $slug, $isBroken, $tags);
    }
}
