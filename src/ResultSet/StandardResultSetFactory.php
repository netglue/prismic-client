<?php

declare(strict_types=1);

namespace Prismic\ResultSet;

use Override;
use Prismic\ResultSet;
use Psr\Http\Message\ResponseInterface;

final class StandardResultSetFactory implements ResultSetFactory
{
    #[Override]
    public function withHttpResponse(ResponseInterface $response): ResultSet
    {
        return StandardResultSet::withHttpResponse($response);
    }

    #[Override]
    public function withJsonObject(object $object): ResultSet
    {
        return StandardResultSet::factory($object);
    }
}
