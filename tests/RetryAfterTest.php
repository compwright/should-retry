<?php

namespace Compwright\ShouldRetry;

use Generator;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RetryAfterTest extends TestCase
{
    static public function generateDefaultTestValues(): Generator
    {
        $response = new Response(429, ['Retry-After' => '75']);
        $request = self::createStub(RequestInterface::class);

        yield [1, $response, $request];
        yield [2, $response, $request];
        yield [3, $response, $request];
        yield [4, $response, $request];
    }

    /**
     * @dataProvider generateDefaultTestValues
     */
    #[DataProvider('generateDefaultTestValues')]
    public function testDefaults(int $retries, Response $response, RequestInterface $request): void
    {
        $after = new RetryAfter();

        $this->assertSame(75000, $after($retries, $response, $request));
    }

    public function testWithCustomHeader(): void
    {
        $after = (new RetryAfter())
            ->setRetryAfterHeader('X-Retry-After');

        $response = (new Response(429, ['X-Retry-After' => '75']));
        $request = $this->createStub(RequestInterface::class);

        $this->assertSame(
            75000,
            $after(2, $response, $request)
        );
    }

    /**
     * @dataProvider generateDefaultTestValues
     */
    #[DataProvider('generateDefaultTestValues')]
    public function testWithCustomFallback(int $retries, Response $response, RequestInterface $request): void
    {
        $after = (new RetryAfter())
            ->setRetryAfterHeader('X-Retry-After')
            ->setFallbackStrategy(
                // Linear backoff
                fn (int $retries): int => $retries * 1000
            );

        $this->assertSame(
            $retries * 1000,
            $after($retries, $response, $request)
        );
    }
}
