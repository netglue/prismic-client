<?php
declare(strict_types=1);

namespace PrismicTest\Serializer;

use Prismic\Document\Fragment\Factory;
use Prismic\Json;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;
use PrismicTest\TestLinkResolver;
use function var_dump;

class HtmlSerializerTest extends TestCase
{
    /** @var HtmlSerializer */
    private $serializer;

    protected function setUp() : void
    {
        parent::setUp();
        $this->serializer = new HtmlSerializer(new TestLinkResolver());
    }

    public function testDocumentBodyIsSerializedWithoutError() : void
    {
        $document = DocumentData::factory(
            Json::decodeObject(
                $this->jsonFixtureByFileName('document.json')
            )
        );

        ($this->serializer)($document->body());
        $this->addToAssertionCount(1);
    }
}
