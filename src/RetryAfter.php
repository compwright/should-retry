<?php declare(strict_types = 1);

namespace Compwright\ShouldRetry;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RetryAfter
{
    private string $retryAfterHeader = 'Retry-After';

    /** @var callable(int, ?ResponseInterface, RequestInterface): int */
    private $fallback;

    public function __construct()
    {
        $this->fallback = new ExponentialBackoff();
    }

    public function setRetryAfterHeader(string $retryAfterHeader): self
    {
        $this->retryAfterHeader = $retryAfterHeader;
        return $this;
    }

    /**
     * @param callable(int, ?ResponseInterface, RequestInterface): int $fallback
     */
    public function setFallbackStrategy(callable $fallback): self
    {
        $this->fallback = $fallback;
        return $this;
    }

    public function __invoke(int $retries, ?ResponseInterface $response, RequestInterface $request): int
    {
        if ($response && $response->hasHeader($this->retryAfterHeader)) {
            return (int) $response->getHeaderLine($this->retryAfterHeader) * 1000;
        }

        return (int) ($this->fallback)($retries, $response, $request);
    }
}
