<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use Prismic\ResultSet;
use Psr\Http\Message\ResponseInterface;

final class StandardResultSetFactory implements ResultSetFactory
{
    public function withHttpResponse(ResponseInterface $response): ResultSet
    {
        return StandardResultSet::withHttpResponse($response);
    }

    public function withJsonObject(object $object): ResultSet
    {
        return StandardResultSet::factory($object);
    }
}
