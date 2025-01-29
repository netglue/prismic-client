<?php

declare(strict_types=1);

namespace PrismicTest\Framework;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use function assert;
use function file_exists;
use function file_get_contents;
use function is_string;
use function sprintf;

abstract class TestCase extends PHPUnitTestCase
{
    protected static function jsonFixtureByFileName(string $fileName): string
    {
        $path = __DIR__ . '/../../fixture/' . $fileName;
        if (! file_exists($path)) {
            self::fail(sprintf(
                'The JSON fixture %s does not exist',
                $fileName,
            ));
        }

        $contents = file_get_contents($path);
        assert(is_string($contents));

        return $contents;
    }
}
