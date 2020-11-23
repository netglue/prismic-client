<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use Prismic\ResultSet;
use Psr\Http\Message\ResponseInterface;

interface ResultSetFactory
{
    public function withHttpResponse(ResponseInterface $response): ResultSet;

    public function withJsonObject(object $object): ResultSet;
}
