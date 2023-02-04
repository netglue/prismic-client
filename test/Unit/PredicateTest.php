<?php

declare(strict_types=1);

namespace PrismicTest;

use DateTime;
use Prismic\Exception\InvalidArgument;
use Prismic\Predicate;
use PrismicTest\Framework\TestCase;

use function assert;
use function serialize;
use function unserialize;
use function var_export;

/** @psalm-import-type ArgType from Predicate */
class PredicateTest extends TestCase
{
    /** @return array<array-key, array{0: string, 1: scalar|list<scalar>, 2:string}> */
    public static function atProvider(): array
    {
        return [
            ['document.type', 'blog-post', '[:d = at(document.type, "blog-post")]'],
            ['my.doc-type.frag-name', 'foo', '[:d = at(my.doc-type.frag-name, "foo")]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = at(document.tags, ["one","two","three"])]'],
            ['my.mytype.boolean', true, '[:d = at(my.mytype.boolean, true)]'],
        ];
    }

    /**
     * @param scalar|list<scalar> $value
     *
     * @dataProvider atProvider
     */
    public function testAtPredicate(string $fragment, $value, string $expect): void
    {
        $predicate = Predicate::at($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    /** @return array<array-key, array{0:string, 1:scalar|list<scalar>, 2:string}> */
    public static function notProvider(): array
    {
        return [
            ['document.type', 'blog-post', '[:d = not(document.type, "blog-post")]'],
            ['my.doc-type.frag-name', 'foo', '[:d = not(my.doc-type.frag-name, "foo")]'],
            ['my.doc-type.price', 100, '[:d = not(my.doc-type.price, 100)]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = not(document.tags, ["one","two","three"])]'],
            ['my.doc.boolean', true, '[:d = not(my.doc.boolean, true)]'],
        ];
    }

    /**
     * @param scalar|list<scalar> $value
     *
     * @dataProvider notProvider
     */
    public function testNotPredicate(string $fragment, $value, string $expect): void
    {
        $predicate = Predicate::not($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    /** @return array<array-key, array{0: string, 1: list<string>, 2:string}> */
    public static function anyProvider(): array
    {
        return [
            ['document.id', ['id1', 'id2'], '[:d = any(document.id, ["id1","id2"])]'],
            ['document.tags', ['one', 'two', 'three'], '[:d = any(document.tags, ["one","two","three"])]'],
            ['document.tags', ['one'], '[:d = any(document.tags, ["one"])]'],
        ];
    }

    /**
     * @param list<string> $value
     *
     * @dataProvider anyProvider
     */
    public function testAnyPredicate(string $fragment, array $value, string $expect): void
    {
        $predicate = Predicate::any($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    /** @return array<array-key, array{0: string, 1: list<string>, 2:string}> */
    public static function inProvider(): array
    {
        return [
            ['document.id', ['id1', 'id2'], '[:d = in(document.id, ["id1","id2"])]'],
            ['my.page.uid', ['uid1', 'uid2'], '[:d = in(my.page.uid, ["uid1","uid2"])]'],
        ];
    }

    /**
     * @param list<string> $value
     *
     * @dataProvider inProvider
     */
    public function testInPredicate(string $fragment, array $value, string $expect): void
    {
        $predicate = Predicate::in($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function testHasPredicate(): void
    {
        $predicate = Predicate::has('my.article.author');
        $this->assertEquals('[:d = has(my.article.author)]', $predicate->q());
    }

    public function testMissingPredicate(): void
    {
        $predicate = Predicate::missing('my.article.author');
        $this->assertEquals('[:d = missing(my.article.author)]', $predicate->q());
    }

    public function testFulltextPredicate(): void
    {
        $predicate = Predicate::fulltext('document', 'some value');
        $this->assertEquals('[:d = fulltext(document, "some value")]', $predicate->q());
    }

    public function testSimilarPredicate(): void
    {
        $predicate = Predicate::similar('someId', 5);
        $this->assertEquals('[:d = similar("someId", 5)]', $predicate->q());
    }

    /** @return array<array-key, array{0: string, 1:numeric, 2:string}> */
    public static function ltProvider(): array
    {
        return [
            ['my.page.num', 1, '[:d = number.lt(my.page.num, 1)]'],
            ['my.page.num', 1.1, '[:d = number.lt(my.page.num, 1.1)]'],
            ['my.page.num', '2', '[:d = number.lt(my.page.num, "2")]'],
        ];
    }

    /** @dataProvider ltProvider */
    public function testNumberLT(string $fragment, int|float|string $value, string $expect): void
    {
        $predicate = Predicate::lt($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function testLtThrowsExceptionForNonNumber(): void
    {
        $this->expectException(InvalidArgument::class);
        Predicate::lt('my.product.price', 'foo');
    }

    /** @return array<array-key, array{0: string, 1:numeric, 2:string}> */
    public static function gtProvider(): array
    {
        return [
            ['my.page.num', 1, '[:d = number.gt(my.page.num, 1)]'],
            ['my.page.num', 1.1, '[:d = number.gt(my.page.num, 1.1)]'],
            ['my.page.num', '2', '[:d = number.gt(my.page.num, "2")]'],
        ];
    }

    /** @dataProvider gtProvider */
    public function testNumberGt(string $fragment, int|float|string $value, string $expect): void
    {
        $predicate = Predicate::gt($fragment, $value);
        $this->assertEquals($expect, $predicate->q());
    }

    public function testGtThrowsExceptionForNonNumber(): void
    {
        $this->expectException(InvalidArgument::class);
        Predicate::gt('my.product.price', 'foo');
    }

    /** @return array<array-key, array{0: string, 1:numeric, 2:numeric, 3:string}> */
    public static function rangeProvider(): array
    {
        return [
            ['my.page.num', 1, 2,  '[:d = number.inRange(my.page.num, 1, 2)]'],
            ['my.page.num', 1.1, 2.2,  '[:d = number.inRange(my.page.num, 1.1, 2.2)]'],
            ['my.page.num', '2', '3', '[:d = number.inRange(my.page.num, "2", "3")]'],
        ];
    }

    /** @dataProvider rangeProvider */
    public function testNumberInRange(string $fragment, int|float|string $low, int|float|string $high, string $expect): void
    {
        $predicate = Predicate::inRange($fragment, $low, $high);
        $this->assertEquals($expect, $predicate->q());
    }

    public function testExceptionThrownByInRangeForNonNumbers(): void
    {
        $this->expectException(InvalidArgument::class);
        Predicate::inRange('my.whatever', 'foo', 'foo');
    }

    public function testDateBefore(): void
    {
        $predicate = Predicate::dateBefore('foo', 1);
        $this->assertEquals('[:d = date.before(foo, 1)]', $predicate->q());
        $predicate = Predicate::dateBefore('foo', '2018-01-01');
        $this->assertEquals('[:d = date.before(foo, "2018-01-01")]', $predicate->q());

        $date = DateTime::createFromFormat('!U', '1');
        $predicate = Predicate::dateBefore('foo', $date);
        $this->assertEquals('[:d = date.before(foo, 1000)]', $predicate->q());
    }

    public function testDateAfter(): void
    {
        $predicate = Predicate::dateAfter('foo', 1);
        $this->assertEquals('[:d = date.after(foo, 1)]', $predicate->q());
        $predicate = Predicate::dateAfter('foo', '2018-01-01');
        $this->assertEquals('[:d = date.after(foo, "2018-01-01")]', $predicate->q());

        $date = DateTime::createFromFormat('!U', '1');
        $predicate = Predicate::dateAfter('foo', $date);
        $this->assertEquals('[:d = date.after(foo, 1000)]', $predicate->q());
    }

    public function testDateBetween(): void
    {
        $predicate = Predicate::dateBetween('foo', 1, 2);
        $this->assertEquals('[:d = date.between(foo, 1, 2)]', $predicate->q());
        $predicate = Predicate::dateBetween('foo', '2018-01-01', '2018-01-02');
        $this->assertEquals('[:d = date.between(foo, "2018-01-01", "2018-01-02")]', $predicate->q());

        $date = DateTime::createFromFormat('!U', '1');
        $predicate = Predicate::dateBetween('foo', $date, $date);
        $this->assertEquals('[:d = date.between(foo, 1000, 1000)]', $predicate->q());
    }

    public function testDayOfMonth(): void
    {
        $predicate = Predicate::dayOfMonth('foo', 1);
        $this->assertEquals('[:d = date.day-of-month(foo, 1)]', $predicate->q());
        $predicate = Predicate::dayOfMonth('foo', '5');
        $this->assertEquals('[:d = date.day-of-month(foo, "5")]', $predicate->q());

        $date = DateTime::createFromFormat('!U', '1');
        $predicate = Predicate::dayOfMonth('foo', $date);
        $this->assertEquals('[:d = date.day-of-month(foo, 1)]', $predicate->q());

        $predicate = Predicate::dayOfMonthBefore('foo', $date);
        $this->assertEquals('[:d = date.day-of-month-before(foo, 1)]', $predicate->q());

        $predicate = Predicate::dayOfMonthAfter('foo', $date);
        $this->assertEquals('[:d = date.day-of-month-after(foo, 1)]', $predicate->q());
    }

    public function testDayOfWeek(): void
    {
        $date = DateTime::createFromFormat('!U', '1');

        $predicate = Predicate::dayOfWeek('foo', $date);
        $this->assertEquals('[:d = date.day-of-week(foo, 4)]', $predicate->q());

        $predicate = Predicate::dayOfWeekAfter('foo', $date);
        $this->assertEquals('[:d = date.day-of-week-after(foo, 4)]', $predicate->q());

        $predicate = Predicate::dayOfWeekBefore('foo', $date);
        $this->assertEquals('[:d = date.day-of-week-before(foo, 4)]', $predicate->q());
    }

    public function testMonth(): void
    {
        $date = DateTime::createFromFormat('!U', '1');

        $predicate = Predicate::month('foo', $date);
        $this->assertEquals('[:d = date.month(foo, 1)]', $predicate->q());

        $predicate = Predicate::monthAfter('foo', $date);
        $this->assertEquals('[:d = date.month-after(foo, 1)]', $predicate->q());

        $predicate = Predicate::monthBefore('foo', $date);
        $this->assertEquals('[:d = date.month-before(foo, 1)]', $predicate->q());
    }

    public function testYear(): void
    {
        $date = DateTime::createFromFormat('!U', '1');

        $predicate = Predicate::year('foo', $date);
        $this->assertEquals('[:d = date.year(foo, 1970)]', $predicate->q());
    }

    public function testHour(): void
    {
        $date = DateTime::createFromFormat('!U', '1');

        $predicate = Predicate::hour('foo', $date);
        $this->assertEquals('[:d = date.hour(foo, 0)]', $predicate->q());

        $predicate = Predicate::hourAfter('foo', $date);
        $this->assertEquals('[:d = date.hour-after(foo, 0)]', $predicate->q());

        $predicate = Predicate::hourBefore('foo', $date);
        $this->assertEquals('[:d = date.hour-before(foo, 0)]', $predicate->q());
    }

    public function testGeopointNear(): void
    {
        $p = Predicate::near('my.store.coordinates', 40.689757, -74.0451453, 15);
        $this->assertEquals('[:d = geopoint.near(my.store.coordinates, 40.689757, -74.0451453, 15)]', $p->q());
    }

    public function testAtPredicateAcceptsBooleanValue(): void
    {
        $p = Predicate::at('my.doc.field', true);
        $this->assertEquals('[:d = at(my.doc.field, true)]', $p->q());
        $p = Predicate::at('my.doc.field', false);
        $this->assertEquals('[:d = at(my.doc.field, false)]', $p->q());
    }

    /**
     * @param scalar|list<scalar> $value
     *
     * @dataProvider atProvider
     */
    public function testPredicatesCanBeCastToString(string $fragment, $value, string $expect): void
    {
        $predicate = Predicate::at($fragment, $value);
        $this->assertEquals($expect, $predicate->__toString());
    }

    /**
     * @param scalar|list<scalar> $value
     *
     * @dataProvider atProvider
     */
    public function testSetState(string $fragment, $value, string $expect): void
    {
        $predicate = Predicate::at($fragment, $value);
        $phpCode = '$rehydrated = ' . var_export($predicate, true) . ';';
        eval($phpCode);
        assert(isset($rehydrated));
        assert($rehydrated instanceof Predicate);
        $this->assertSame($expect, $rehydrated->q());
    }

    /**
     * @param scalar|list<scalar> $value
     *
     * @dataProvider atProvider
     */
    public function testPredicatesAreSerializable(string $fragment, $value, string $expect): void
    {
        $predicate = Predicate::at($fragment, $value);
        $rehydrated = unserialize(serialize($predicate));
        assert($rehydrated instanceof Predicate);
        $this->assertSame($expect, $rehydrated->q());
    }

    public function testHasTag(): void
    {
        $predicate = Predicate::hasTag('foo');
        $this->assertEquals('[:d = at(document.tags, ["foo"])]', (string) $predicate);
    }
}
