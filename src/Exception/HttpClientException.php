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
        return new self(
            self::createMissingDefaultImplementationMessage('\Psr\Http\Client\ClientInterface', 'httpClient')
        );
    }

    public static function noDefaultHttpRequestFactory(): self
    {
        return new self(
            self::createMissingDefaultImplementationMessage(
                '\Psr\Http\Message\RequestFactoryInterface',
                'httpRequestFactory'
            )
        );
    }

    public static function noDefaultUriFactory(): self
    {
        return new self(
            self::createMissingDefaultImplementationMessage('\Psr\Http\Message\UriFactoryInterface', 'uriFactory')
        );
    }

    public static function noDefaultStreamFactory(): self
    {
        return new self(
            self::createMissingDefaultImplementationMessage('\Psr\Http\Message\StreamFactoryInterface', 'streamFactory')
        );
    }

    private static function createMissingDefaultImplementationMessage(
        string $missingInterface,
        string $configOption
    ): string {
        return "No default implementation of the ${missingInterface} found. Install 'guzzlehttp/guzzle' via composer or provide a custom PSR-17 http-factory via ClientOptions['${configOption}‘].";
    }
}
