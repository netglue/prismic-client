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
        $this->assertInstanceOf(TextElement::class, $richText->get(0));
        $this->assertInstanceOf(UnorderedList::class, $richText->get(1));
        $this->assertCount(2, $richText->get(1));
        $this->assertInstanceOf(TextElement::class, $richText->get(2));
        $this->assertInstanceOf(OrderedList::class, $richText->get(3));
        $this->assertCount(2, $richText->get(3));
        $this->assertInstanceOf(TextElement::class, $richText->get(4));
        $this->assertInstanceOf(UnorderedList::class, $richText->get(5));
        $this->assertCount(1, $richText->get(5));
        $this->assertInstanceOf(OrderedList::class, $richText->get(6));
        $this->assertCount(1, $richText->get(6));
    }

    public function testThatListItemOrderIsRetained(): void
    {
        $richText = $this->listItemsFixture();

        $list = $richText->filter(static function (Fragment $fragment): bool {
            return $fragment instanceof OrderedList;
        })->first();

        assert($list instanceof OrderedList);

        $this->assertSame('Ordered 1', $list->first()->text());
        $this->assertSame('Ordered 2', $list->last()->text());
    }
}
