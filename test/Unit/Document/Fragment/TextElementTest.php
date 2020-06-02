<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\TextElement;
use PrismicTest\Framework\TestCase;
use TypeError;

class TextElementTest extends TestCase
{
    public function testConstructor() : TextElement
    {
        $text = TextElement::new(
            'paragraph',
            'Some Words',
            [],
            'groovy'
        );
        $this->addToAssertionCount(1);

        return $text;
    }

    /** @depends testConstructor */
    public function testTypeIsExpectedValue(TextElement $text) : void
    {
        $this->assertSame('paragraph', $text->type());
    }

    /** @depends testConstructor */
    public function testTextHasExpectedValue(TextElement $text) : void
    {
        $this->assertSame('Some Words', $text->text());
    }

    /** @depends testConstructor */
    public function testThatElementWithLabelIsDeemedHavingLabel(TextElement $text) : void
    {
        $this->assertTrue($text->hasLabel());
    }

    /** @depends testConstructor */
    public function testLabelIsExpectedValue(TextElement $text) : void
    {
        $this->assertSame('groovy', $text->label());
    }

    /** @depends testConstructor */
    public function testThatAnElementWithNonEmptyTextIsDeemedNonEmpty(TextElement $text) : void
    {
        $this->assertFalse($text->isEmpty());
    }

    public function testThatAnElementWithEmptyTextIsConsideredEmpty() : void
    {
        $empty = TextElement::new('foo', '', [], null);
        $this->assertTrue($empty->isEmpty());
    }

    public function testThatLabelIsOptionallyNull() : void
    {
        $text = TextElement::new('foo', '', [], null);
        $this->assertNull($text->label());
        $this->assertFalse($text->hasLabel());
    }

    public function testThatNullTextValueWillYieldEmptyString() : void
    {
        $text = TextElement::new('foo', null, [], null);
        $this->assertSame('', $text->text());
        $this->assertTrue($text->isEmpty());
    }

    public function testThatItIsATypeErrorForSpanToContainNonSpan() : void
    {
        $this->expectException(TypeError::class);
        TextElement::new('foo', null, ['dingdong'], null);
    }

    /** @return mixed[] */
    public function headingTypeProvider() : iterable
    {
        return [
            TextElement::TYPE_HEADING1 => [TextElement::TYPE_HEADING1],
            TextElement::TYPE_HEADING2 => [TextElement::TYPE_HEADING2],
            TextElement::TYPE_HEADING3 => [TextElement::TYPE_HEADING3],
            TextElement::TYPE_HEADING4 => [TextElement::TYPE_HEADING4],
            TextElement::TYPE_HEADING5 => [TextElement::TYPE_HEADING5],
            TextElement::TYPE_HEADING6 => [TextElement::TYPE_HEADING6],
        ];
    }

    /** @dataProvider headingTypeProvider */
    public function testThatHeadingsAreConsideredHeadings(string $type) : void
    {
        $text = TextElement::new($type, 'Foo', [], null);
        $this->assertTrue($text->isHeading());
    }

    public function testThatListItemsAreConsideredListItems() : void
    {
        $li = TextElement::new(TextElement::TYPE_UNORDERED_LIST_ITEM, 'foo', [], null);
        $this->assertTrue($li->isListItem());
        $this->assertTrue($li->isUnorderedListItem());
        $this->assertFalse($li->isOrderedListItem());
        $li = TextElement::new(TextElement::TYPE_ORDERED_LIST_ITEM, 'foo', [], null);
        $this->assertTrue($li->isListItem());
        $this->assertTrue($li->isOrderedListItem());
        $this->assertFalse($li->isUnorderedListItem());
    }

    public function testThatAParagraphIsConsideredAParagraph() : void
    {
        $p = TextElement::new(TextElement::TYPE_PARAGRAPH, 'foo', [], null);
        $this->assertTrue($p->isParagraph());
    }

    /** @return mixed[] */
    public function typeCheckProvider() : iterable
    {
        return [
            TextElement::TYPE_HEADING1            => [TextElement::TYPE_HEADING1,            false, true,  false, false, false, false],
            TextElement::TYPE_HEADING2            => [TextElement::TYPE_HEADING2,            false, true,  false, false, false, false],
            TextElement::TYPE_HEADING3            => [TextElement::TYPE_HEADING3,            false, true,  false, false, false, false],
            TextElement::TYPE_HEADING4            => [TextElement::TYPE_HEADING4,            false, true,  false, false, false, false],
            TextElement::TYPE_HEADING5            => [TextElement::TYPE_HEADING5,            false, true,  false, false, false, false],
            TextElement::TYPE_HEADING6            => [TextElement::TYPE_HEADING6,            false, true,  false, false, false, false],
            TextElement::TYPE_PARAGRAPH           => [TextElement::TYPE_PARAGRAPH,           false, false, true,  false, false, false],
            TextElement::TYPE_PREFORMATTED        => [TextElement::TYPE_PREFORMATTED,        false, false, false, false, false, false],
            TextElement::TYPE_ORDERED_LIST_ITEM   => [TextElement::TYPE_ORDERED_LIST_ITEM,   false, false, false, true,  true,  false],
            TextElement::TYPE_UNORDERED_LIST_ITEM => [TextElement::TYPE_UNORDERED_LIST_ITEM, false, false, false, true,  false, true],
        ];
    }

    /** @dataProvider typeCheckProvider */
    public function testTypes(string $type, bool $empty, bool $heading, bool $paragraph, bool $list, bool $ordered, bool $unordered) : void
    {
        $item = TextElement::new($type, 'Foo', [], null);
        $this->assertSame($empty, $item->isEmpty());
        $this->assertSame($heading, $item->isHeading());
        $this->assertSame($paragraph, $item->isParagraph());
        $this->assertSame($list, $item->isListItem());
        $this->assertSame($ordered, $item->isOrderedListItem());
        $this->assertSame($unordered, $item->isUnorderedListItem());
    }
}
