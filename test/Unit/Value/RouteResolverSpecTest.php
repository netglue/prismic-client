<?php

declare(strict_types=1);

namespace PrismicTest\Value;

use PHPUnit\Framework\TestCase;
use Prismic\Value\RouteResolverSpec;

class RouteResolverSpecTest extends TestCase
{
    public function testExpectedStringValueWithEmptyResolvers(): void
    {
        $expect = '{"type":"mine","path":"\/:uid","resolvers":{}}';

        self::assertEquals($expect, (string) (new RouteResolverSpec('mine', '/:uid', [])));
    }

    public function testExpectedStringValueWithNonEmptyResolvers(): void
    {
        $expect = '{"type":"mine","path":"\/:category\/:uid","resolvers":{"category":"some-prop"}}';

        self::assertEquals(
            $expect,
            (string) (new RouteResolverSpec('mine', '/:category/:uid', ['category' => 'some-prop']))
        );
    }

    public function testExportedSpecCanBeRehydrated(): void
    {
        $spec = RouteResolverSpec::__set_state([
            'type' => 'mine',
            'path' => '/path',
            'resolvers' => ['foo' => 'bar'],
        ]);

        $expect = '{"type":"mine","path":"\/path","resolvers":{"foo":"bar"}}';

        self::assertEquals($expect, (string) $spec);
    }
}
