<?php

declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prismic\Document\Fragment\Table;
use Prismic\Json;
use Prismic\Value\DocumentData;

use function assert;
use function file_get_contents;

final class TableTest extends TestCase
{
    /**
     * @return list<array{
     *     fixture: non-empty-string,
     *     hasHead: bool,
     *     hasBody: bool,
     *     isEmpty: bool,
     * }>
     */
    public static function tableFixtureProvider(): array
    {
        return [
            [
                'fixture' => __DIR__ . '/../../../fixture/tables/body-only.json',
                'hasHead' => false,
                'hasBody' => true,
                'isEmpty' => false,
            ],
            [
                'fixture' => __DIR__ . '/../../../fixture/tables/both-header-and-footer.json',
                'hasHead' => true,
                'hasBody' => true,
                'isEmpty' => false,
            ],
            [
                'fixture' => __DIR__ . '/../../../fixture/tables/empty-body.json',
                'hasHead' => false,
                'hasBody' => true,
                'isEmpty' => true,
            ],
            [
                'fixture' => __DIR__ . '/../../../fixture/tables/header-only.json',
                'hasHead' => true,
                'hasBody' => false,
                'isEmpty' => false,
            ],
        ];
    }

    #[DataProvider('tableFixtureProvider')]
    public function testTables(string $fixture, bool $hasHead, bool $hasBody, bool $isEmpty): void
    {
        $json = file_get_contents($fixture);
        assert($json !== false);
        $document = DocumentData::factory(Json::decodeObject($json));

        $table = $document->content()->get('table');
        self::assertInstanceOf(Table::class, $table);

        self::assertSame($hasHead, $table->hasHead());
        self::assertSame($hasBody, $table->hasBody());
        self::assertSame($isEmpty, $table->isEmpty());

        foreach ($table->body as $row) {
            self::assertNotEmpty($row->key);
            foreach ($row as $cell) {
                self::assertFalse($cell->isHeaderCell());
                self::assertNotEmpty($cell->key);
            }
        }

        if (! $table->hasHead()) {
            return;
        }

        foreach ($table->head as $cell) {
            self::assertTrue($cell->isHeaderCell());
            self::assertNotEmpty($cell->key);
        }
    }
}
