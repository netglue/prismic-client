<?php
declare(strict_types=1);

namespace PrismicSmokeTest;

use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prismic\Api;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

use function file_exists;
use function getenv;

class TestCase extends PHPUnitTestCase
{
    /** @var CacheItemPoolInterface|null */
    private $cache;

    /** @var ClientInterface|null */
    private $httpClient;

    protected function psrCachePool() : CacheItemPoolInterface
    {
        if (! $this->cache) {
            $this->cache = new ApcuAdapter('PrismicTests', 0);
        }

        return $this->cache;
    }

    protected function httpClient() : ClientInterface
    {
        if (! $this->httpClient) {
            $this->httpClient = new PluginClient(
                HttpClientDiscovery::find(),
                [new CachePlugin($this->psrCachePool(), Psr17FactoryDiscovery::findStreamFactory())]
            );
        }

        return $this->httpClient;
    }

    /** @return string[] */
    protected function compileEndPoints() : array
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

        $configured = require $configPath;
        $configured = $configured['endpoints'] ?? [];
        foreach ($configured as $spec) {
            if (! isset($spec['url'])) {
                continue;
            }

            $endpoints[$spec['url']] = $spec['token'] ?? null;
        }

        return $endpoints;
    }

    /** @return Api[][] */
    public function apiDataProvider() : iterable
    {
        foreach ($this->apiInstances() as $url => $api) {
            yield $url => [$api];
        }
    }

    /** @return Api[] */
    protected function apiInstances() : iterable
    {
        foreach ($this->compileEndPoints() as $url => $token) {
            $api = Api::get($url, $token, $this->httpClient());

            yield $api->host() => $api;
        }
    }
}
