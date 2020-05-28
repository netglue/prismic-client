<?php
declare(strict_types=1);

namespace PrismicTest;

use PrismicTest\Framework\TestCase;
use function http_build_query;
use function parse_str;
use const PHP_QUERY_RFC3986;

class SanityTest extends TestCase
{
    public function testThatParseStrDoesDecode() : void
    {
        $url = '/api/v2/documents/search?ref=some-ref&pageSize=20&q=%5B%5B:d%20%3D%20at(document.id,%20%22someId%22)%5D%5D';
        $params = [];
        parse_str($url, $params);
        $this->assertSame('[[:d = at(document.id, "someId")]]', $params['q']);
    }

    public function testThatHttpBuildQueryEncodes() : void
    {
        $params = [
            'q' => '[[d: = at(document.id, "someId")]]',
        ];
        $url = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $this->assertEquals('q=%5B%5Bd%3A%20%3D%20at%28document.id%2C%20%22someId%22%29%5D%5D', $url);
    }
}
