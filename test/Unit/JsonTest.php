<?php

declare(strict_types=1);

namespace PrismicTest;

use JsonException;
use Prismic\Exception\JsonError;
use Prismic\Json;
use PrismicTest\Framework\TestCase;

use const STDOUT;

class JsonTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function notObjects(): array
    {
        return [
            'Array' => ['[{"foo":"bar"},{"foo":"bar"}]'],
            'False' => ['false'],
            'True' => ['true'],
        ];
    }

    /** @dataProvider notObjects */
    public function testObjectUnserializeFailure(string $payload): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('The given payload cannot be unserialized as an object');
        Json::decodeObject($payload);
    }

    public function testArrayUnserializeFailure(): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('The given payload cannot be unserialized as an array');
        Json::decodeArray('false');
    }

    /** @return array<string, array{0:string}> */
    public static function invalidJson(): array
    {
        return [
            'Trailing Comma' => ['[{"foo":"bar"},]'],
            'Word' => ['foo'],
            'Empty string' => [''],
        ];
    }

    /** @dataProvider invalidJson */
    public function testInvalidJsonInDecodeObject(string $payload): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        Json::decodeObject($payload);
    }

    /** @dataProvider invalidJson */
    public function testInvalidJsonInDecodeArray(string $payload): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        Json::decodeArray($payload);
    }

    /** @dataProvider invalidJson */
    public function testInvalidJsonInDecode(string $payload): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        Json::decode($payload, true);
    }

    public function testUnEncodableData(): void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to encode the given data to a JSON string');
        Json::encode(STDOUT);
    }

    public function testThatThePayloadIsPreservedByTheException(): void
    {
        $payload = '{"foo",}';
        try {
            Json::decode($payload, true);
            $this->fail();
        } catch (JsonError $error) {
            $this->assertSame($payload, $error->payload());
            $previous = $error->getPrevious();
            $this->assertInstanceOf(JsonException::class, $previous);
            $this->assertSame(
                $error->getCode(),
                $previous->getCode(),
            );
        }
    }

    public function testSuccessfulDecode(): void
    {
        $value = Json::decode('{"foo":"bar"}', true);
        self::assertSame(['foo' => 'bar'], $value);
    }
}
