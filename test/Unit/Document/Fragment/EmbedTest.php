<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Generator;
use Prismic\Document\Fragment\Collection;
use Prismic\Document\Fragment\Embed;
use Prismic\Document\Fragment\Factory;
use Prismic\Exception\InvalidArgument;
use Prismic\Json;
use PrismicTest\Framework\TestCase;

use function assert;

class EmbedTest extends TestCase
{
    private static function embedCollection(): Collection
    {
        $data = Json::decodeObject(self::jsonFixtureByFileName('embed-types.json'));
        $collection = Factory::factory($data);
        assert($collection instanceof Collection);
        self::assertContainsOnlyInstancesOf(Embed::class, $collection);

        return $collection;
    }

    private function tweet(): Embed
    {
        $tweet = self::embedCollection()->get('twitter');
        assert($tweet instanceof Embed);

        return $tweet;
    }

    /** @return Generator<array-key, array<Embed>> */
    public static function embedProvider(): Generator
    {
        foreach (self::embedCollection() as $key => $embed) {
            assert($embed instanceof Embed);

            yield $key => [$embed];
        }
    }

    /** @dataProvider embedProvider */
    public function testThatEmbedAreNotConsideredEmpty(Embed $embed): void
    {
        $this->assertFalse($embed->isEmpty());
    }

    public function testThatProviderSpecificPropertiesAreAccessible(): void
    {
        $tweet = $this->tweet();
        $this->assertSame('Twitter', $tweet->attribute('provider_name'));
        $this->assertIsString($tweet->attribute('author_url'));
        $this->assertIsString($tweet->attribute('author_name'));
    }

    /** @dataProvider embedProvider */
    public function testThatAttributesAreTheSameAsCorrespondingNamedMethods(Embed $embed): void
    {
        $this->assertSame($embed->url(), $embed->attribute('embed_url'));
        $this->assertSame($embed->type(), $embed->attribute('type'));
        $this->assertSame($embed->provider(), $embed->attribute('provider_name'));
        $this->assertSame($embed->html(), $embed->attribute('html'));
    }

    /** @dataProvider embedProvider */
    public function testThatAttributesArrayCanBeRetrieved(Embed $embed): void
    {
        $attributes = $embed->attributes();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('provider_name', $attributes);
    }

    /** @psalm-suppress InvalidArgument */
    public function testAnExceptionIsThrownSettingAnAttributeToANonScalarValue(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('A scalar argument was expected but');
        Embed::new('mytype', 'someurl', 'foo', 'foo', 1, 1, [
            'atr' => ['not scalar'],
        ]);
    }

    public function testEmbedHasTheExpectedValues(): void
    {
        $embed = Embed::new(
            'foo',
            'some-url',
            'some-one',
            '<html>',
            10,
            20,
            [],
        );

        self::assertEquals('foo', $embed->type());
        self::assertEquals('some-url', $embed->url());
        self::assertEquals('some-one', $embed->provider());
        self::assertEquals('<html>', $embed->html());
        self::assertEquals(10, $embed->width());
        self::assertEquals(20, $embed->height());
    }
}
