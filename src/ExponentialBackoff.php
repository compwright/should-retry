<?php declare(strict_types = 1);

namespace Compwright\ShouldRetry;

class ExponentialBackoff
{
    public function __invoke(int $retries): int
    {
        return (int) 2 ** ($retries - 1) * 1000;
    }
}
