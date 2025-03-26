<?php

declare(strict_types=1);

namespace PrismicSmokeTest\Serializer;

use PHPUnit\Framework\Attributes\DataProvider;
use Prismic\Api;
use Prismic\Serializer\HtmlSerializer;
use PrismicSmokeTest\TestCase;
use PrismicTest\TestLinkResolver;

final class HtmlSerializerTest extends TestCase
{
    private HtmlSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new HtmlSerializer(new TestLinkResolver());
    }

    #[DataProvider('apiDataProvider')]
    public function testThatAllDocumentsCanBeRenderedWithOutError(Api $api): void
    {
        $this->expectNotToPerformAssertions();
        foreach ($api->findAll($api->createQuery()) as $document) {
            ($this->serializer)($document->data()->content());
        }
    }
}
