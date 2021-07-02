<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Generator;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prismic\Api;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

use function assert;
use function file_exists;
use function getenv;
use function is_array;
use function is_string;

class TestCase extends PHPUnitTestCase
{
    /** @var CacheItemPoolInterface|null */
    private $cache;

    /** @var ClientInterface|null */
    private $httpClient;

    protected function psrCachePool(): CacheItemPoolInterface
    {
        if (! $this->cache) {
            $this->cache = new ApcuAdapter('PrismicTests', 0);
        }

        return $this->cache;
    }

    protected function httpClient(): ClientInterface
    {
        if (! $this->httpClient) {
            $this->httpClient = new PluginClient(
                HttpClientDiscovery::find(),
                [new CachePlugin($this->psrCachePool(), Psr17FactoryDiscovery::findStreamFactory())]
            );
        }

        return $this->httpClient;
    }

    /** @return array<string, string|null> */
    protected function compileEndPoints(): array
    {
        $endpoints = [];
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
    public function apiDataProvider(): Generator
    {
        foreach ($this->apiInstances() as $url => $api) {
            yield $url => [$api];
        }
    }

    /** @return Generator<string, Api> */
    protected function apiInstances(): Generator
    {
        foreach ($this->compileEndPoints() as $url => $token) {
            $api = Api::get($url, $token, $this->httpClient());

            yield $api->host() => $api;
        }
    }
}
