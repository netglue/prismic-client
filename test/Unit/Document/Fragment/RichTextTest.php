<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\Factory;
use Prismic\Document\Fragment\OrderedList;
use Prismic\Document\Fragment\RichText;
use Prismic\Document\Fragment\TextElement;
use Prismic\Document\Fragment\UnorderedList;
use Prismic\Json;
use PrismicTest\Framework\TestCase;

use function assert;

class RichTextTest extends TestCase
{
    private function listItemsFixture(): RichText
    {
        $collection = Factory::factory(Json::decodeObject($this->jsonFixtureByFileName('list-items.json')));
        assert($collection instanceof Collection);
        $richText = $collection->get('rich_text');
        assert($richText instanceof RichText);

        return $richText;
    }

    public function testThatListItemsAreCollectedAsExpected(): void
    {
        $richText = $this->listItemsFixture();
        self::assertInstanceOf(TextElement::class, $richText->get(0));
        self::assertInstanceOf(UnorderedList::class, $richText->get(1));
        self::assertCount(2, $richText->get(1));
        self::assertInstanceOf(TextElement::class, $richText->get(2));
        self::assertInstanceOf(OrderedList::class, $richText->get(3));
        self::assertCount(2, $richText->get(3));
        self::assertInstanceOf(TextElement::class, $richText->get(4));
        self::assertInstanceOf(UnorderedList::class, $richText->get(5));
        self::assertCount(1, $richText->get(5));
        self::assertInstanceOf(OrderedList::class, $richText->get(6));
        self::assertCount(1, $richText->get(6));
    }

    public function testThatListItemOrderIsRetained(): void
    {
        $richText = $this->listItemsFixture();

        $list = $richText->filter(static function (Fragment $fragment): bool {
            return $fragment instanceof OrderedList;
        })->first();

        assert($list instanceof OrderedList);

        self::assertSame('Ordered 1', $list->first()->text());
        self::assertSame('Ordered 2', $list->last()->text());
    }

    public function testThatRichTextFragmentsCanBeCastToAString(): void
    {
        $richText = $this->listItemsFixture();
        self::assertStringStartsWith('Initial Paragraph', (string) $richText);
        self::assertStringEndsWith('Final Paragraph', (string) $richText);
    }

    public function testThatOrderedListsCanBeCastToAString(): void
    {
        $richText = $this->listItemsFixture();
        $list = $richText->get(3);
        assert($list instanceof OrderedList);

        $expect = <<<TEXT
            Ordered 1
            Ordered 2
            TEXT;

        self::assertEquals($expect, (string) $list);
    }
}
