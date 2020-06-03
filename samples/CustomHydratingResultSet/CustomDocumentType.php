<?php
declare(strict_types=1);

namespace Prismic\Example\CustomHydratingResultSet;

use Prismic\Document;
use Prismic\Document\DocumentDataConsumer;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\TextElement;
use Prismic\Value\DocumentData;
use RuntimeException;

class CustomDocumentType implements Document
{
    use DocumentDataConsumer;

    public function __construct(DocumentData $data)
    {
        $this->data = $data;
    }

    public function get(string $name) :? Fragment
    {
        return $this->data->content()->get($name);
    }

    public function getTitle() : string
    {
        $title = $this->get('title');
        if (! $title instanceof TextElement || $title->isEmpty()) {
            throw new RuntimeException('There’s no title for this document…');
        }

        return $title->text();
    }
}
