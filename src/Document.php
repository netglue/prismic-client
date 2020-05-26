<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;

interface Document
{
    public function id() : string;

    public function uid() : string;

    public function type() : string;

    /** @return string[] */
    public function tags() : iterable;

    /** @return string[] */
    public function slugs() : iterable;

    public function lang() : string;

    public function firstPublished() : DateTimeInterface;

    public function lastPublished() : DateTimeInterface;
}
