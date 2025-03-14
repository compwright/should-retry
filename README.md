# compwright/should-retry

## Installation

    $ composer require compwright/should-retry

## Usage with Guzzle HTTP library

The popular Guzzle HTTP library includes a retry middleware:

```php
use Compwright\ShouldRetry\ShouldRetry;
use Compwright\ShouldRetry\RetryAfter;

$handler = GuzzleHttp\HandlerStack::create();

$handler->push(
    GuzzleHttp\Middleware::retry(
        new ShouldRetry(),
        new RetryAfter()
    ),
    'retry'
);

$client = new GuzzleHttp\Client([
    'handler' => $handler,
]);
```

## Configuration

Both ShouldRetry and RetryAfter are configurable via setters:

```php
$shouldRetry = (new ShouldRetry())
    ->setMaxRetries(5)
    ->setRetryOnStatusCodes(429)
    ->setLogger($logger); // attach PSR-3 debug logger

$retryAfter = (new RetryAfter())
    ->setRetryAfterHeader('x-rate-limit-reset')
    ->setFallbackStrategy($fallback);
```

Default configuration:

* Retry up to 3 times
* Retry on 429, 500, 502, 503, 504 error codes
* When retrying, wait `Retry-After` seconds
* If header is missing, use exponential backoff

## License

MIT License
