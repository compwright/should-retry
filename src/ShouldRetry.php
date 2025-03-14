<?php declare(strict_types = 1);

namespace Compwright\ShouldRetry;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class ShouldRetry implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private int $maxRetries = 3;

    /** @var int[] */
    private array $retryOnStatusCodes = [429, 500, 502, 503, 504];

    public function setMaxRetries(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;
        return $this;
    }

    public function setRetryOnStatusCodes(int ...$statusCodes): self
    {
        $this->retryOnStatusCodes = $statusCodes;
        return $this;
    }

    public function __invoke(int $retries, RequestInterface $request, ?ResponseInterface $response, ?Throwable $exception): bool
    {
        if ($retries >= $this->maxRetries) {
            if ($this->logger) {
                $this->logger->debug(\sprintf(
                    "Failed to access %s after %d attempts.",
                    $request->getUri(),
                    $retries
                ));
            }
            return false;
        }

        if ($exception instanceof NetworkExceptionInterface) {
            if ($this->logger) {
                $this->logger->debug(\sprintf(
                    "Connection failed to %s, retrying (%d/%d)",
                    $request->getUri(),
                    $retries + 1,
                    $this->maxRetries
                ));
            }
            return true;
        }

        if ($response && in_array($response->getStatusCode(), $this->retryOnStatusCodes, true)) {
            if ($this->logger) {
                $this->logger->debug(\sprintf(
                    "HTTP %d from %s, will retry (%d/%d)",
                    $response->getStatusCode(),
                    $request->getUri(),
                    $retries + 1,
                    $this->maxRetries
                ));
            }
            return true;
        }

        if ($this->logger) {
            $this->logger->debug(\sprintf(
                "Failed to access %s, not retryable.",
                $request->getUri()
            ));
        }

        return false;
    }
}
