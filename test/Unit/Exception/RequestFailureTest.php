<?php
declare(strict_types=1);

namespace PrismicTest\Exception;

use Laminas\Diactoros\Response\JsonResponse;
use Prismic\Exception\PreviewTokenExpired;
use Prismic\Exception\RequestFailure;
use PrismicTest\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestFailureTest extends TestCase
{
    public function testWithClientErrorReturnsPreviewTokenExpiredInstanceWhenResponseBodyMatchesExpectedValue() : void
    {
        $response = new JsonResponse([
            'error' => PreviewTokenExpired::EXPECTED_ERROR_MESSAGE,
        ], 400);

        $request = $this->createMock(RequestInterface::class);

        $error = RequestFailure::withClientError($request, $response);
        $this->assertInstanceOf(PreviewTokenExpired::class, $error);
    }
}
