<?php

namespace Compwright\ShouldRetry;

use Generator;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ShouldRetryTest extends TestCase
{
    static public function generateTestCases(): Generator
    {
        $requestStub = self::createStub(RequestInterface::class);

        yield 'max retries' => [false, 3, $requestStub, null, null];

        yield 'not retryable' => [false, 0, $requestStub, null, null];

        yield 'connection error' => [true, 2, $requestStub, null, new ConnectException('Test connection error', $requestStub)];

        yield 'http 429, attempt 1' => [true, 0, $requestStub, new Response(429), null];
        yield 'http 429, attempt 2' => [true, 1, $requestStub, new Response(429), null];
        yield 'http 429, attempt 3' => [true, 2, $requestStub, new Response(429), null];
        yield 'http 429, attempt 4' => [false, 3, $requestStub, new Response(429), null];
    }

    /**
     * @dataProvider generateTestCases
     */
    #[DataProvider('generateTestCases')]
    public function testInvoke(bool $expected, int $retries, RequestInterface $request, ?ResponseInterface $response, ?Throwable $exception): void
    {
        $shouldRetry = new ShouldRetry();
        $this->assertSame($expected, $shouldRetry($retries, $request, $response, $exception));
    }
}
