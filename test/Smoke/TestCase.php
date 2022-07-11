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
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function file_exists;
use function getenv;

class TestCase extends PHPUnitTestCase
{
    /** @var CacheItemPoolInterface|null */
    private static $cache;

    /** @var ClientInterface|null */
    private $httpClient;

    protected function psrCachePool(): CacheItemPoolInterface
    {
        if (! self::$cache) {
            self::$cache = new ArrayAdapter(600, false);
        }

        return self::$cache;
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

        $configured = $content['endpoints'] ?? [];

        foreach ($configured as $spec) {
            $endpoints[$spec['url']] = $spec['token'];
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
