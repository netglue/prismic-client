<?php

declare(strict_types=1);

namespace PrismicSmokeTest\Serializer;

use Prismic\Api;
use Prismic\Serializer\HtmlSerializer;
use PrismicSmokeTest\TestCase;
use PrismicTest\TestLinkResolver;

class HtmlSerializerTest extends TestCase
{
    /** @var HtmlSerializer */
    private $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new HtmlSerializer(new TestLinkResolver());
    }

    /** @dataProvider apiDataProvider */
    public function testThatAllDocumentsCanBeRenderedWithOutError(Api $api): void
    {
        $documentCount = 0;
        foreach ($api->findAll($api->createQuery()) as $document) {
            ($this->serializer)($document->content());
            $documentCount++;
        }

        $this->addToAssertionCount($documentCount);
    }
}
