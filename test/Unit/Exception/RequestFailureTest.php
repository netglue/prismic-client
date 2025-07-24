<?php

declare(strict_types=1);

namespace PrismicTest\Exception;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\Serializer;
use Prismic\Exception\PreviewTokenExpired;
use Prismic\Exception\RequestFailure;
use Prismic\Exception\ResponseTooLarge;
use PrismicTest\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function file_get_contents;

final class RequestFailureTest extends TestCase
{
    public function testWithClientErrorReturnsPreviewTokenExpiredInstanceWhenResponseBodyMatchesExpectedValue(): void
    {
        $response = new JsonResponse(['error' => 'Preview token has expired'], 400);
        $request = $this->createMock(RequestInterface::class);

        $error = RequestFailure::withClientError($request, $response);
        $this->assertInstanceOf(PreviewTokenExpired::class, $error);
    }

    public function testExpectedResponseTooLargeError(): void
    {
        $message = file_get_contents(__DIR__ . '/../../fixture/responses/422.http');
        self::assertIsString($message);
        $response = Serializer::fromString($message);
        $request = $this->createMock(RequestInterface::class);

        $error = RequestFailure::withClientError($request, $response);
        self::assertInstanceOf(ResponseTooLarge::class, $error);

        self::assertSame($request, $error->getRequest());
        self::assertSame($response, $error->getResponse());

        self::assertStringContainsString('Your request was rejected because the response would be too large', $error->getMessage());
        self::assertStringContainsString('Your response is:  6.03 MO', $error->getMessage());
    }
}
