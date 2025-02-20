# SseClient
Client to receive server-sent events (from OpenAI API runner for example)

## Installation
The recommended way to install SseClient is through
[Composer](https://getcomposer.org/).
```bash
composer require volkerschulz/sse-client
```

## Usage
Minimal:
```php
use volkerschulz\SseClient;

$client = new SseClient('https://example.com');
foreach($client->getEvents() as $event) {
    // Handle single event 
}
```

[List of all options](/docs/options.md)

## Security

If you discover a security vulnerability within this package, please send an email to security@volkerschulz.de. All security vulnerabilities will be promptly addressed. Please do not disclose security-related issues publicly until a fix has been announced. 

## License

This package is made available under the MIT License (MIT). Please see [License File](LICENSE) for more information.
