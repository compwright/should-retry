<?php

namespace Compwright\ShouldRetry;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExponentialBackoffTest extends TestCase
{
    static public function generateTestValues(): Generator
    {
        yield [0, 0];
        yield [1, 1000];
        yield [2, 2000];
        yield [3, 4000];
        yield [4, 8000];
    }

    /**
     * @dataProvider generateTestValues
     */
    #[DataProvider('generateTestValues')]
    public function testBackoffCalculateDelay(int $retry, int $ms): void
    {
        $backoff = new ExponentialBackoff();
        $this->assertSame($ms, $backoff($retry));
    }
}
