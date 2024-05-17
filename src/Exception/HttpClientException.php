<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Exception;

class HttpClientException extends \RuntimeException
{
    public static function noDefaultHttpClient(): self
    {
        return new self("No default implementation of the \\Psr\\Http\\Client\\ClientInterface found. Install 'guzzlehttp/guzzle' via composer or provide a custom PSR-18 client via ClientOptions['httpClient‘].");
    }

    public static function noDefaultHttpRequestFactory(): self
    {
        return new self("No default implementation of the \\Psr\\Http\\Message\\RequestFactoryInterface found. Install 'guzzlehttp/guzzle' via composer or provide a custom PSR-17 http-factory via ClientOptions['httpRequestFactory‘].");
    }
}
