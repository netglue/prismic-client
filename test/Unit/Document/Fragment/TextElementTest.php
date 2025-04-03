<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Prismic\Document\Fragment\Span;
use Prismic\Document\Fragment\TextElement;
use PrismicTest\Framework\TestCase;
use TypeError;

final class TextElementTest extends TestCase
{
    public function testConstructor(): TextElement
    {
        $text = TextElement::new(
            'paragraph',
            'Some Words',
            [],
            'groovy',
        );
        $this->expectNotToPerformAssertions();

        return $text;
    }

    #[Depends('testConstructor')]
    public function testTypeIsExpectedValue(TextElement $text): void
    {
        self::assertSame('paragraph', $text->type());
    }

    #[Depends('testConstructor')]
    public function testTextHasExpectedValue(TextElement $text): void
    {
        self::assertSame('Some Words', $text->text());
    }

    #[Depends('testConstructor')]
    public function testThatElementWithLabelIsDeemedHavingLabel(TextElement $text): void
    {
        self::assertTrue($text->hasLabel());
    }

    #[Depends('testConstructor')]
    public function testLabelIsExpectedValue(TextElement $text): void
    {
        self::assertSame('groovy', $text->label());
    }

    #[Depends('testConstructor')]
    public function testThatAnElementWithNonEmptyTextIsDeemedNonEmpty(TextElement $text): void
    {
        self::assertFalse($text->isEmpty());
    }

    public function testThatAnElementWithEmptyTextIsConsideredEmpty(): void
    {
        $empty = TextElement::new(TextElement::TYPE_PARAGRAPH, '', [], null);
        self::assertTrue($empty->isEmpty());
    }

    public function testThatLabelIsOptionallyNull(): void
    {
        $text = TextElement::new(TextElement::TYPE_PARAGRAPH, '', [], null);
        self::assertNull($text->label());
        self::assertFalse($text->hasLabel());
    }

    public function testThatNullTextValueWillYieldEmptyString(): void
    {
        $text = TextElement::new(TextElement::TYPE_PARAGRAPH, null, [], null);
        self::assertSame('', $text->text());
        self::assertTrue($text->isEmpty());
    }

    public function testThatItIsATypeErrorForSpanToContainNonSpan(): void
    {
        $this->expectException(TypeError::class);
        /** @psalm-suppress InvalidArgument */
        TextElement::new(TextElement::TYPE_PARAGRAPH, null, ['dingdong'], null);
    }

    /** @return array<TextElement::TYPE_*, array{0: TextElement::TYPE_*}> */
    public static function headingTypeProvider(): array
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

    /** @param TextElement::TYPE_* $type */
    #[DataProvider('headingTypeProvider')]
    public function testThatHeadingsAreConsideredHeadings(string $type): void
    {
        $text = TextElement::new($type, 'Foo', [], null);
        self::assertTrue($text->isHeading());
    }

    public function testThatListItemsAreConsideredListItems(): void
    {
        $li = TextElement::new(TextElement::TYPE_UNORDERED_LIST_ITEM, 'foo', [], null);
        self::assertTrue($li->isListItem());
        self::assertTrue($li->isUnorderedListItem());
        self::assertFalse($li->isOrderedListItem());
        $li = TextElement::new(TextElement::TYPE_ORDERED_LIST_ITEM, 'foo', [], null);
        self::assertTrue($li->isListItem());
        self::assertTrue($li->isOrderedListItem());
        self::assertFalse($li->isUnorderedListItem());
    }

    public function testThatAParagraphIsConsideredAParagraph(): void
    {
        $p = TextElement::new(TextElement::TYPE_PARAGRAPH, 'foo', [], null);
        self::assertTrue($p->isParagraph());
    }

    /** @return array<TextElement::TYPE_*, array{0: TextElement::TYPE_*, 1: bool, 2: bool, 3: bool, 4: bool, 5: bool, 6: bool}> */
    public static function typeCheckProvider(): array
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

    /** @param TextElement::TYPE_* $type */
    #[DataProvider('typeCheckProvider')]
    public function testTypes(string $type, bool $empty, bool $heading, bool $paragraph, bool $list, bool $ordered, bool $unordered): void
    {
        $item = TextElement::new($type, 'Foo', [], null);
        self::assertSame($empty, $item->isEmpty());
        self::assertSame($heading, $item->isHeading());
        self::assertSame($paragraph, $item->isParagraph());
        self::assertSame($list, $item->isListItem());
        self::assertSame($ordered, $item->isOrderedListItem());
        self::assertSame($unordered, $item->isUnorderedListItem());
    }

    public function testThatTextElementsCanBeCastToAString(): void
    {
        $item = TextElement::new(TextElement::TYPE_HEADING1, 'Heading', [], null);
        self::assertEquals('Heading', (string) $item);
    }

    public function testTypeCanBeMutated(): void
    {
        $item = TextElement::new(TextElement::TYPE_HEADING1, 'Heading', [], null);
        $copy = $item->withDifferentType(TextElement::TYPE_PARAGRAPH);

        self::assertNotSame($item, $copy);
        self::assertSame(TextElement::TYPE_PARAGRAPH, $copy->type());
    }

    public function testLabelsCanBeReplaced(): void
    {
        $item = TextElement::new(TextElement::TYPE_HEADING1, 'Heading', [], 'class1');
        $copy = $item->withReplacementLabel('label2');

        self::assertNotSame($item, $copy);

        self::assertSame('class1', $item->label());
        self::assertSame('label2', $copy->label());
    }

    public function testLabelsCanBeAdded(): void
    {
        $item = TextElement::new(TextElement::TYPE_HEADING1, 'Heading', [], 'class1');
        $copy = $item->withAddedLabel('label2');

        self::assertNotSame($item, $copy);

        self::assertSame('class1', $item->label());
        self::assertSame('class1 label2', $copy->label());

        $item2 = $copy->withAddedLabel('block-tail')
            ->withAddedLabel('inline-fart');

        self::assertSame('class1 label2 block-tail inline-fart', $item2->label());
    }

    public function testSpansCanBeRemoved(): void
    {
        $spans = [
            Span::new('strong', 0, 3, null, null),
        ];
        $item = TextElement::new(TextElement::TYPE_HEADING1, 'Heading', $spans, null);

        $copy = $item->withoutSpans();

        self::assertNotSame($item, $copy);

        self::assertSame($spans, $item->spans());
        self::assertSame([], $copy->spans());
    }
}
