<?php

declare(strict_types=1);

namespace PrismicTest\Exception;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Prismic\Exception\PreviewTokenExpired;
use PrismicTest\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function file_get_contents;

final class PreviewTokenExpiredTest extends TestCase
{
    /** @return array<string, array{0: array<string, string>, 1:int}> */
    public static function possibleResponseBodiesThatShouldRepresentExpiredPreviews(): array
    {
        return [
            'Legacy version of error message' => [
                ['error' => 'Preview token expired'],
                400,
            ],
            'Error message observed mid to late 2021' => [
                [
                    'type' => 'api_security_error',
                    'message' => 'This preview token has expired',
                    'oauth_initiate' => 'https://something.prismic.io/auth',
                    'oauth_token' => 'https://something.prismic.io/auth/token',
                ],
                404,
            ],
            'Yet another change to the error format around 2024 maybe' => [
                [
                    'type' => 'api_notfound_error',
                    'message' => 'https://whatever.prismic.io/previews/aID57hcAACYAP-nz:aIIBzxcAACYAQYax?websitePreviewId=Z-aJqxEAACgA_GAh not found',
                ],
                404,
            ],
        ];
    }

    /** @return array<string, array{0: array<string, string>, 1:int}> */
    public static function responseBodiesThatShouldNotRepresentExpiredTokens(): array
    {
        return [
            'No message or error key' => [
                ['nuts' => 'I’m a fucking teapot.'],
                418,
            ],
        ];
    }

    /** @param array<string, string> $bodyPayload */
    #[DataProvider('responseBodiesThatShouldNotRepresentExpiredTokens')]
    public function testIsPreviewTokenExpiryIsFalseWhenErrorMessageDoesNotMatchExpectedValue(array $bodyPayload, int $responseCode): void
    {
        $response = new JsonResponse($bodyPayload, $responseCode);

        $this->assertFalse(PreviewTokenExpired::isPreviewTokenExpiry($response));
    }

    /** @param array<string, string> $bodyPayload */
    #[DataProvider('possibleResponseBodiesThatShouldRepresentExpiredPreviews')]
    public function testThatSimulatedTokenExpiryResponseYieldsExpectedExceptionProperties(array $bodyPayload, int $responseCode): void
    {
        $response = new JsonResponse($bodyPayload, $responseCode);
        $request = $this->createStub(RequestInterface::class);
        $error = PreviewTokenExpired::with($request, $response);

        $this->assertStringContainsString('The preview token provided has expired', $error->getMessage());
        $this->assertSame($responseCode, $error->getCode());
        $this->assertSame($request, $error->getRequest());
        $this->assertSame($response, $error->getResponse());
    }

    /** @param array<string, string> $bodyPayload */
    #[DataProvider('possibleResponseBodiesThatShouldRepresentExpiredPreviews')]
    public function testVariousResponsesWillBeConsideredTokenExpiryConditions(array $bodyPayload, int $responseCode): void
    {
        $response = new JsonResponse($bodyPayload, $responseCode);
        self::assertTrue(PreviewTokenExpired::isPreviewTokenExpiry($response));
    }

    public function testPrismicFuckedUp422ResponseIsNotAPreviewExpiryException(): void
    {
        $message = file_get_contents(__DIR__ . '/../../fixture/responses/422.http');
        self::assertIsString($message);
        $response = Response\Serializer::fromString($message);

        self::assertFalse(PreviewTokenExpired::isPreviewTokenExpiry($response));
    }
}
