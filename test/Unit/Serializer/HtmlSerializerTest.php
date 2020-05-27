<?php
declare(strict_types=1);

namespace PrismicTest\Serializer;

use Prismic\Document\Fragment;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\Fragment\OrderedList;
use Prismic\Document\Fragment\RichText;
use Prismic\Document\Fragment\UnorderedList;
use Prismic\Document\FragmentCollection;
use Prismic\Json;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Value\DocumentData;
use PrismicTest\Framework\TestCase;
use PrismicTest\TestLinkResolver;
use function assert;

class HtmlSerializerTest extends TestCase
{
    /** @var HtmlSerializer */
    private $serializer;

    protected function setUp() : void
    {
        parent::setUp();
        $this->serializer = new HtmlSerializer(new TestLinkResolver());
    }

    private function richTextSpansFixture() : RichText
    {
        $body = Factory::factory(Json::decodeObject($this->jsonFixtureByFileName('rich-text-spans.json')));
        assert($body instanceof FragmentCollection);
        $richText = $body->get('rich_text');
        assert($richText instanceof RichText);

        return $richText;
    }

    private function richTextBlockElementsFixture() : RichText
    {
        $body = Factory::factory(Json::decodeObject($this->jsonFixtureByFileName('block-elements.json')));
        assert($body instanceof FragmentCollection);
        $richText = $body->get('rich_text');
        assert($richText instanceof RichText);

        return $richText;
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

    public function testListSerialisation() : void
    {
        $collection = (new Factory())(Json::decodeObject($this->jsonFixtureByFileName('list-items.json')));
        assert($collection instanceof FragmentCollection);
        $richText = $collection->get('rich_text');
        assert($richText instanceof RichText);
        $list = $richText->filter(static function (Fragment $fragment) : bool {
            return $fragment instanceof UnorderedList;
        })->first();
        assert($list instanceof UnorderedList);
        $markup = $this->serializer->__invoke($list);
        $this->assertEquals('<ul><li>Unordered 1</li><li>Unordered 2</li></ul>', $markup);
    }

    public function testAnEmptyListWillYieldAnEmptyString() : void
    {
        $list = OrderedList::new([]);
        $this->assertSame('', ($this->serializer)($list));
    }

    /** @return mixed[] */
    public function richTextSpanMarkupData() : iterable
    {
        return [
            'Bold & Italic' => [
                0,
                '<p>Paragraph with multiple <strong>bold</strong> and <em>italic</em> spans.</p>',
            ],
            'Labels in text' => [
                1,
                '<p>Paragraph with <span class="foo">inline</span> labels <span class="bar">spanning</span> words.</p>',
            ],
            'Paragraph labelled at block level' => [
                2,
                '<p class="foo">Paragraph labelled at block level</p>',
            ],
            'Paragraph with web link' => [
                3,
                '<p>Paragraph with <a href="https://example.com" target="_blank">web link</a>.</p>',
            ],
            'Paragraph with broken link' => [
                4,
                '<p>Paragraph with broken link.</p>',
            ],
            'Paragraph with document link' => [
                5,
                '<p>Paragraph with <a href="document://doc-uid">document link</a>.</p>',
            ],
            'Empty Paragraph' => [
                6,
                '',
            ],
            'Paragraph with nested spans' => [
                7,
                '<p>Paragraph <strong>with <span class="test-label">nested spans</span> of <em>several</em> types</strong>.</p>',
            ],
            'Paragraph with spans at the same index' => [
                8,
                '<p>Paragraph with multiple <strong><em><span class="test-label">spans at the same index</span></em></strong>.</p>',
            ],
        ];
    }

    /** @dataProvider richTextSpanMarkupData */
    public function testSpansCorrectlyWrapText(int $fragmentIndex, string $expectedMarkup) : void
    {
        $richText = $this->richTextSpansFixture();
        $this->assertEquals(
            $expectedMarkup,
            ($this->serializer)($richText->offsetGet($fragmentIndex))
        );
    }

    /** @return mixed[] */
    public function richTextBlockElementsData() : iterable
    {
        return [
            'H1' => [
                0,
                '<h1>Heading 1</h1>',
            ],
            'H2' => [
                1,
                '<h2>Heading 2</h2>',
            ],
            'H3' => [
                2,
                '<h3>Heading 3</h3>',
            ],
            'H4' => [
                3,
                '<h4>Heading 4</h4>',
            ],
            'H5' => [
                4,
                '<h5>Heading 5</h5>',
            ],
            'H6' => [
                5,
                '<h6>Heading 6</h6>',
            ],
            'p' => [
                6,
                '<p>Paragraph</p>',
            ],
            'pre' => [
                7,
                '<pre>pre-formatted text</pre>',
            ],
            'Special characters' => [
                8,
                '<p>Escape special &amp; &lt;tags&gt;</p>',
            ],
        ];
    }

    /** @dataProvider richTextBlockElementsData */
    public function testBlockElementsMarkup(int $fragmentIndex, string $expectedMarkup) : void
    {
        $richText = $this->richTextBlockElementsFixture();
        $this->assertEquals(
            $expectedMarkup,
            ($this->serializer)($richText->offsetGet($fragmentIndex))
        );
    }
}
