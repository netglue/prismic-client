<?php
declare(strict_types=1);

namespace PrismicTest\Framework;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use function file_exists;
use function file_get_contents;
use function sprintf;

abstract class TestCase extends PHPUnitTestCase
{
    protected function jsonFixtureByFileName(string $fileName) : string
    {
        $path = __DIR__ . '/../../fixture/' . $fileName;
        if (! file_exists($path)) {
            $this->fail(sprintf(
                'The JSON fixture %s does not exist',
                $fileName
            ));
        }

        return file_get_contents($path);
    }
}
