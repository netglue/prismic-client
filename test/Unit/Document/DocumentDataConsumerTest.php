<?php

declare(strict_types=1);

namespace PrismicTest\Document;

use Prismic\Document;
use Prismic\Document\DocumentDataConsumer;
use Prismic\Json;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;

class DocumentDataConsumerTest extends TestCase
{
    private DocumentData $document;

    private Document $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->document = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document.json'),
            ),
        );

        $this->subject = new class ($this->document) implements Document
        {
            use DocumentDataConsumer;

            public function __construct(DocumentData $data)
            {
                $this->data = $data;
            }
        };
    }

    public function testProxyMethods(): void
    {
        $this->assertSame($this->document->id(), $this->subject->id());
        $this->assertSame($this->document->uid(), $this->subject->uid());
        $this->assertSame($this->document->type(), $this->subject->type());
        $this->assertSame($this->document->tags(), $this->subject->tags());
        $this->assertSame($this->document->lang(), $this->subject->lang());
        $this->assertSame($this->document->firstPublished(), $this->subject->firstPublished());
        $this->assertSame($this->document->lastPublished(), $this->subject->lastPublished());
        $this->assertSame($this->document->translations(), $this->subject->translations());
        $this->assertSame($this->document->data(), $this->subject->data());
        $this->assertSame($this->document->asLink()->id(), $this->subject->asLink()->id());
    }
}
