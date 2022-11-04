<?php

declare(strict_types=1);

namespace PrismicSmokeTest\Serializer;

use Prismic\Api;
use Prismic\Serializer\HtmlSerializer;
use PrismicSmokeTest\TestCase;
use PrismicTest\TestLinkResolver;

class HtmlSerializerTest extends TestCase
{
    private HtmlSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new HtmlSerializer(new TestLinkResolver());
    }

    /** @dataProvider apiDataProvider */
    public function testThatAllDocumentsCanBeRenderedWithOutError(Api $api): void
    {
        $this->expectNotToPerformAssertions();
        foreach ($api->findAll($api->createQuery()) as $document) {
            ($this->serializer)($document->data()->content());
        }
    }
}
