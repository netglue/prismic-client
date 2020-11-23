<?php

declare(strict_types=1);

namespace PrismicTest\Exception;

use Laminas\Diactoros\Response\JsonResponse;
use Prismic\Exception\PreviewTokenExpired;
use PrismicTest\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class PreviewTokenExpiredTest extends TestCase
{
    public function testIsPreviewTokenExpiryIsFalseWhenErrorMessageDoesNotMatchExpectedValue(): void
    {
        $response = new JsonResponse(['error' => 'Not expected message']);

        $this->assertFalse(PreviewTokenExpired::isPreviewTokenExpiry($response));
    }

    public function testIsPreviewTokenExpiryIsTrueWhenErrorMessageMatchesExpectedValue(): void
    {
        $response = new JsonResponse([
            'error' => PreviewTokenExpired::EXPECTED_ERROR_MESSAGE,
        ]);

        $this->assertTrue(PreviewTokenExpired::isPreviewTokenExpiry($response));
    }

    public function testThatSimulatedTokenExpiryResponseYieldsExpectedExceptionProperties(): void
    {
        $response = new JsonResponse([
            'error' => PreviewTokenExpired::EXPECTED_ERROR_MESSAGE,
        ], 400);

        $request = $this->createMock(RequestInterface::class);

        $error = PreviewTokenExpired::with($request, $response);

        $this->assertStringContainsString('The preview token provided has expired', $error->getMessage());
        $this->assertSame(400, $error->getCode());
        $this->assertSame($request, $error->getRequest());
        $this->assertSame($response, $error->getResponse());
    }
}
