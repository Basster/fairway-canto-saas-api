<?php
declare(strict_types=1);

namespace Fairway\CantoSaasApi\Exception;


class HttpClientException extends \RuntimeException
{
    public static function noDefaultHttpClient(): self
    {
        return new self("No default implementation of the \\Psr\\Http\\Client\\ClientInterface found. Install 'guzzlehttp/guzzle' via composer or provide a custom PSR-18 client via ClientOptions['httpClient‘].");
    }
}
