<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Generator;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prismic\Api;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function assert;
use function file_exists;
use function getenv;
use function is_array;
use function is_string;

class TestCase extends PHPUnitTestCase
{
    private static CacheItemPoolInterface|null $cache = null;

    private static ClientInterface|null $httpClient = null;

    protected static function psrCachePool(): CacheItemPoolInterface
    {
        if (! self::$cache) {
            self::$cache = new ArrayAdapter(600, false);
        }

        return self::$cache;
    }

    protected static function httpClient(): ClientInterface
    {
        if (! self::$httpClient) {
            self::$httpClient = new PluginClient(
                Psr18ClientDiscovery::find(),
                [new CachePlugin(self::psrCachePool(), Psr17FactoryDiscovery::findStreamFactory())],
            );
        }

        return self::$httpClient;
    }

    /** @return array<string, string|null> */
    protected static function compileEndPoints(): array
    {
        $endpoints = ['https://primo.cdn.prismic.io/api/v2' => null];
        $repo = getenv('PRISMIC_REPO') ?: null;
        $token = getenv('PRISMIC_TOKEN') ?: null;
        if ($repo) {
            $endpoints[$repo] = $token;
        }

        $configPath = __DIR__ . '/../config/config.php';

        if (! file_exists($configPath)) {
            return $endpoints;
        }

        $content = require $configPath;
        if (! is_array($content)) {
            return $endpoints;
        }

        $configured = $content['endpoints'] ?? [];
        assert(is_array($configured));

        foreach ($configured as $spec) {
            assert(is_array($spec));
            $url = isset($spec['url']) && is_string($spec['url']) ? $spec['url'] : null;
            $token = isset($spec['token']) && is_string($spec['token']) ? $spec['token'] : null;

            if (! $url) {
                continue;
            }

            $endpoints[$url] = $token;
        }

        return $endpoints;
    }

    /** @return Generator<string, array{0: Api}> */
    public static function apiDataProvider(): Generator
    {
        foreach (self::apiInstances() as $url => $api) {
            yield $url => [$api];
        }
    }

    /** @return Generator<string, Api> */
    protected static function apiInstances(): Generator
    {
        foreach (self::compileEndPoints() as $url => $token) {
            $api = Api::get($url, $token, self::httpClient());

            yield $api->host() => $api;
        }
    }
}
