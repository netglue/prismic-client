<?php

declare(strict_types=1);

namespace Prismic\Document;

use DateTimeInterface;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Value\DocumentData;
use Prismic\Value\Translation;

trait DocumentDataConsumer
{
    private DocumentData $data;

    public function id(): string
    {
        return $this->data->id();
    }

    public function uid(): string|null
    {
        return $this->data->uid();
    }

    public function type(): string
    {
        return $this->data->type();
    }

    /** @return string[] */
    public function tags(): iterable
    {
        return $this->data->tags();
    }

    public function lang(): string
    {
        return $this->data->lang();
    }

    public function firstPublished(): DateTimeInterface
    {
        return $this->data->firstPublished();
    }

    public function lastPublished(): DateTimeInterface
    {
        return $this->data->lastPublished();
    }

    /** @return Translation[] */
    public function translations(): iterable
    {
        return $this->data->translations();
    }

    public function asLink(): DocumentLink
    {
        return $this->data->asLink();
    }

    public function data(): DocumentData
    {
        return $this->data;
    }

    public function url(): string|null
    {
        return $this->data->url();
    }
}
