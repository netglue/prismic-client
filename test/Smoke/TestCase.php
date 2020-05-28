<?php
declare(strict_types=1);

namespace PrismicSmokeTest;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Prismic\Api;
use function file_exists;
use function getenv;

class TestCase extends PHPUnitTestCase
{
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
            $api = Api::get($url, $token);

            yield $api->host() => $api;
        }
    }
}
