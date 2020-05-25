<?php
declare(strict_types=1);

namespace Prismic;

use Psr\Http\Client\ClientInterface;

final class Api
{
    /** @var ClientInterface */
    private $httpClient;

    private function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}
