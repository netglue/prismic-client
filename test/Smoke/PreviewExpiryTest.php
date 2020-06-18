<?php
declare(strict_types=1);

namespace PrismicSmokeTest;

use Prismic\Api;
use Prismic\Exception\PreviewTokenExpired;

use function sprintf;
use function str_replace;

class PreviewExpiryTest extends TestCase
{
    /** @dataProvider apiDataProvider */
    public function testThatRequestsToInvalidPreviewUrlsOnTheSameHostYieldPreviewExpiryExceptions(Api $api) : void
    {
        // Tokens come back from the api with the cdn subdomain stripped
        $host = str_replace('.cdn.', '.', $api->host());
        $previewToken = sprintf('https://%s/previews/invalid-token', $host);

        $this->expectException(PreviewTokenExpired::class);
        $api->previewSession($previewToken);
    }
}
